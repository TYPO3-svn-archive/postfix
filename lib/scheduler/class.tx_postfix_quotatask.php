<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2012 Dirk Wildt (http://wildt.at.die-netzmacher.de/)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Class "tx_postfix_QuotaTask" provides procedures for check quota and control postfix accounts
 *
 * @author        Dirk Wildt (http://wildt.at.die-netzmacher.de/)
 * @package        TYPO3
 * @subpackage    postfix
* @version       1.1.0
* @since         1.1.0
 */
class tx_postfix_QuotaTask extends tx_scheduler_Task {

  /**
    * Extension key
    *
    * @var string $extKey
    */
    var $extKey = 'postfix';
    
  /**
    * Extension configuration by the extension manager
    *
    * @var array $extConf
    */
    var $extConf;

  /**
    * DRS mode: display prompt in every case
    *
    * @var boolean $drsModeAll
    */
    var $drsModeAll;
    
  /**
    * DRS mode: display prompt in error case only 
    *
    * @var boolean $drsModeError
    */
    var $drsModeError;
    
  /**
    * DRS mode: display prompt in warning case only
    *
    * @var boolean $drsModeWarn
    */
    var $drsModeWarn;
    
  /**
    * DRS mode: display prompt in info case only
    *
    * @var boolean $drsModeInfo
    */
    var $drsModeInfo;
    
  /**
    * DRS mode: display prompt in performance case 
    *
    * @var boolean $drsModePerformance
    */
    var $drsModePerformance;
    
  /**
    * DRS mode: display prompt in quotaTask case 
    *
    * @var boolean $drsModeQuotaTask
    */
    var $drsModeQuotaTask;
    
  /**
    * DRS mode: display prompt in sql case
    *
    * @var boolean $drsModeSql
    */
    var $drsModeSql;
    
  /**
    * An email address to be used during the process
    *
    * @var string $postfix_postfixAdminEmail
    */
    var $postfix_postfixAdminEmail;
    
  /**
    * All Postfix accounts returned from database
    *
    * @var array $accountsData
    */
    var $accountsData;
    

  /**
    * execute( )  : Function executed from the Scheduler.
    *               * Sends an email
    *
    * @return boolean
    * @version       1.1.0
    * @since         1.1.0
    */
  public function execute( )
  {
    $success = true;
    
      // Get the extension configuration by the extension manager
    $this->extConf = unserialize( $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['postfix'] );

      // Init the DRS - Development Reporting System
    $this->initDRS( );
    
      // Init timetracking, set the starttime
    $this->timeTracking_init( );
    $debugTrailLevel = 1;
    $this->timeTracking_log( $debugTrailLevel, 'START' );

      // Get the data of each postfix account from the database
    $this->accountsData = $this->initAccountsData( );
    
      // Loop all accounts;
    if( ! $this->executeLoopAllAccounts( ) )
    {
      $success = false;
    }
      // Loop all accounts;
    
      // RETURN : the success
    $this->timeTracking_log( $debugTrailLevel, 'END' );
    return $success;
  }

  
  
  /***********************************************
   *
   * Initials
   *
   **********************************************/
  
  /**
    * initAccountsData( ) : Get the data of each postfix account from the database.
    *
    * @return   array   $rows
    * @version       1.1.0
    * @since         1.1.0
    */
  private function initAccountsData( )
  {
      // Query
    $select_fields  = "
                        uid, 
                        username, 
                        first_name, 
                        last_name, 
                        email,
                        CONCAT( tx_postfix_homedir, '/', tx_postfix_maildir ) AS 'pathToFolder', 
                        tx_postfix_quota
                      ";
    $from_table     = "fe_users";
    $where_clause   = "(tx_postfix_homedir IS NOT NULL OR tx_postfix_maildir IS NOT NULL)";
    $groupBy        = null;
    $orderBy        = "pathToFolder";
    $limit          = null;
      // Query

      // DRS
    if( $this->drsModeSql )
    {
      $query  = $GLOBALS['TYPO3_DB']->SELECTquery
                (
                  $select_fields,
                  $from_table,
                  $where_clause,
                  $groupBy,
                  $orderBy,
                  $limit
                );
      $prompt = $query;
      t3lib_div::devlog( '[tx_postfix_QuotaTask] ' . $prompt, $this->extKey, 0 );
    }
      // DRS

      // SELECT
    $res =  $GLOBALS['TYPO3_DB']->exec_SELECTquery
            (
              $select_fields,
              $from_table,
              $where_clause,
              $groupBy,
              $orderBy,
              $limit
            );
      // SELECT

      // Get array with TYPO3Groups
    $rows = array( );
    while( $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc( $res ) )
    {
      $rows[ ]  = ( array ) $row;
    }
      // Get array with TYPO3Groups
    
      // DRS
    if( $this->drsModeSql )
    {
      $prompt = '#' . count( $rows ) . ' accounts found.';
      t3lib_div::devlog( '[tx_postfix_QuotaTask] ' . $prompt, $this->extKey, 0 );
    }
    return $rows;
  }

  /**
    * initDRS( )  : Init the DRS - Development Reporting System
    *               
    * @return void
    * @version       1.1.0
    * @since         1.1.0
    */
  private function initDRS( )
  {

    if( $this->extConf['drsMode'] == 'Disabled' )
    {
      return;
    }
    
    $this->drsModeAll   = true;
    $this->drsModeError = true;
    $this->drsModeWarn  = true;
    $this->drsModeInfo  = true;

    $prompt = 'DRS - Development Reporting System: ' . $this->extConf['drsMode'];
    t3lib_div::devlog( '[tx_postfix_QuotaTask] ' . $prompt, $this->extKey, 0 );

    switch( $this->extConf['drsMode'] )
    {
      case( 'Enabled (for debugging only!)' ):
        $this->drsModePerformance = true;
        $this->drsModeQuotaTask   = true;
        $this->drsModeSql         = true;
        break;
      default:
          // :TODO: Error msg per email to admin
        $this->drsModePerformance = true;
        $this->drsModeQuotaTask   = true;
        $this->drsModeSql         = true;
        $prompt = 'DRS mode isn\'t defined.';
        t3lib_div::devlog( '[tx_postfix_QuotaTask] ' . $prompt, $this->extKey, 3 );
        break;
    }
  }

  /**
    * executeLoopAllAccounts( )  : Function executed from the Scheduler.
    *               * Sends an email
    *
    * @return boolean
    * @version       1.1.0
    * @since         1.1.0
    */
  private function executeLoopAllAccounts( )
  {
    $success = false;
    
    foreach( $this->accountsData as $accountData )
    {
        // DRS
      if( $this->drsModeQuotaTask )
      {
        $prompt = $accountData['email'];
        t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 0 );
      }
        // DRS
    }
    
      // RETURN : no email address is given
    if ( empty( $this->postfix_postfixAdminEmail ) ) 
    {
        // DRS
      if( $this->drsModeQuotaTask )
      {
        $prompt = 'email address is missing for the Postfix admin.';
        t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 2 );
      }
        // DRS
      return $success;
    }
      // RETURN : no email address is given
      
      // Get call method
    if( basename( PATH_thisScript ) == 'cli_dispatch.phpsh')
    {
      $calledBy = 'CLI module dispatcher';
      $site     = '-';
    }
    else
    {
      $calledBy = 'TYPO3 backend';
      $site     = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
    }
      // Get call method

      // Get execution information
    $exec = $this->getExecution();

    $strAccountData = var_export( $this->accountsData, true );

    $start    = $exec->getStart();
    $end      = $exec->getEnd();
    $interval = $exec->getInterval();
    $multiple = $exec->getMultiple();
    $cronCmd  = $exec->getCronCmd();
    $mailBody =
      'POSTFIX QUOTA' . PHP_EOL .
      '- - - - - - - - - - - - - - - -' . PHP_EOL .
      'UID: '       . $this->taskUid . PHP_EOL .
      'Sitename: '  . $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] . PHP_EOL .
      'Site: ' . $site . PHP_EOL .
      'Called by: ' . $calledBy . PHP_EOL .
      'tstamp: ' . date('Y-m-d H:i:s') . ' [' . time() . ']' . PHP_EOL .
      'start: ' . date('Y-m-d H:i:s', $start) . ' [' . $start . ']' . PHP_EOL .
      'end: ' . ((empty($end)) ? '-' : (date('Y-m-d H:i:s', $end) . ' [' . $end . ']')) . PHP_EOL .
      'interval: ' . $interval . PHP_EOL .
      'multiple: ' . ($multiple ? 'yes' : 'no') . PHP_EOL .
      'cronCmd: ' . ($cronCmd ? $cronCmd : 'not used') . PHP_EOL .
      PHP_EOL .
      $strAccountData . PHP_EOL .
      'XXX'
      ;

      // Prepare mailer and send the mail
    try 
    {
      /** @var $mailer t3lib_mail_message */
      $mailer = t3lib_div::makeInstance('t3lib_mail_message');
      $mailer->setFrom(array($this->postfix_postfixAdminEmail => 'POSTFIX QUOTA'));
      $mailer->setReplyTo(array($this->postfix_postfixAdminEmail => 'POSTFIX QUOTA'));
      $mailer->setSubject('POSTFIX QUOTA');
      $mailer->setBody($mailBody);
      $mailer->setTo($this->postfix_postfixAdminEmail);
      
      $mailsSend  = $mailer->send( );
      $success    = ( $mailsSend > 0 );
    } 
    catch( Exception $e )
    {
      throw new t3lib_exception( $e->getMessage( ) );
    }

      // DRS
    if( $this->drsModeQuotaTask )
    {
      switch( $success )
      {
        case( false ):
          $prompt = 'Undefined error. Test email couldn\'t sent to "' . $this->postfix_postfixAdminEmail . '"';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
          break;
        case( true ):
        default:
          $prompt = 'Test email is sent to "' . $this->postfix_postfixAdminEmail . '"';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, -1 );
          break;
      }
    }

    return $success;
  }
    /***********************************************
   *
   * DRS - Development Reporting System
   *
   **********************************************/



/**
 * drs_debugTrail( ): Returns class, method and line of the call of this method.
 *                    The calling method is a debug method - if it is called by another
 *                    method, please set the level in the calling method to 2.
 *
 * $param   integer   $level      : integer
 *
 * @param    [type]        $$level: ...
 * @return    array        $arr_return : with elements class, method, line and prompt
 * @version 3.9.9
 * @since   3.9.9
 */
  public function drs_debugTrail( $level = 1 )
  {
    $arr_return = null; 
    
      // Get the debug trail
    $debugTrail_str = t3lib_utility_Debug::debugTrail( );

      // Get debug trail elements
    $debugTrail_arr = explode( '//', $debugTrail_str );

      // Get class, method
    $classMethodLine = $debugTrail_arr[ count( $debugTrail_arr) - ( $level + 2 )];
    list( $classMethod ) = explode ( '#', $classMethodLine );
    list($class, $method ) = explode( '->', $classMethod );
      // Get class, method

      // Get line
    $classMethodLine = $debugTrail_arr[ count( $debugTrail_arr) - ( $level + 1 )];
    list( $dummy, $line ) = explode ( '#', $classMethodLine );
    unset( $dummy );
      // Get line

      // RETURN content
    $arr_return['class']  = trim( $class );
    $arr_return['method'] = trim( $method );
    $arr_return['line']   = trim( $line );
    $arr_return['prompt'] = $arr_return['class'] . '::' . $arr_return['method'] . ' (' . $arr_return['line'] . ')';

    return $arr_return;
      // RETURN content
  }



  
  
  /***********************************************
   *
   * Time tracking
   *
   **********************************************/

  /**
    * timeTracking_init( ):  Init the timetracking object. Set the global $startTime.
    *
    * @return    void
    * @version       1.1.0
    * @since         1.1.0
    */
  private function timeTracking_init( )
  {
      // Init the timetracking object
    require_once( PATH_t3lib . 'class.t3lib_timetrack.php' );
    $this->TT = new t3lib_timeTrack;
    $this->TT->start( );
      // Init the timetracking object

      // Set the global $startTime.
    $this->tt_startTime = $this->TT->getDifferenceToStarttime();
  }

  /**
    * timeTracking_log( ): Prompts a message in devLog with current run time in miliseconds
    *
    * @param    integer        $debugTrailLevel  : level for the debug trail
    * @param    string        $line             : current line in calling method
    * @param    string        $prompt           : The prompt for devlog.
    * @return    void
    * @version       1.1.0
    * @since         1.1.0
    */
  private function timeTracking_log( $debugTrailLevel, $prompt )
  {
      // RETURN: DRS shouldn't report performance prompts
    if( ! $this->drsModePerformance )
    {
      return;
    }
      // RETURN: DRS shouldn't report performance prompts

      // Get the current time
    $endTime = $this->TT->getDifferenceToStarttime( );

    $debugTrail = $this->drs_debugTrail( $debugTrailLevel );

    // Prompt the current time
    $mSec   = sprintf("%05d", ( $endTime - $this->tt_startTime ) );
    $prompt = $mSec . ' ms ### ' . 
              $debugTrail['prompt'] . ': ' . $prompt;
    t3lib_div::devLog( $prompt, $this->extKey, 0 );

    $timeOfPrevProcess = $endTime - $this->tt_prevEndTime;
    
    switch( true )
    {
      case( $timeOfPrevProcess >= 10000 ):
        $this->tt_prevPrompt = 3;
        $prompt = 'Previous process needs more than 10 sec (' . $timeOfPrevProcess / 1000 . ' sec)';
        t3lib_div::devLog('[WARN/PERFORMANCE] ' . $prompt, $this->extKey, 3 );
        break;
      case( $timeOfPrevProcess >= 250 ):
        $this->tt_prevPrompt = 2;
        $prompt = 'Previous process needs more than 0.25 sec (' . $timeOfPrevProcess / 1000 . ' sec)';
        t3lib_div::devLog('[WARN/PERFORMANCE] ' . $prompt, $this->extKey, 2 );
        break;
      default:
        $this->tt_prevPrompt = 0;
        // Do nothing
    }
    $this->tt_prevEndTime = $endTime;
  }

  /**
    * timeTracking_prompt( ):  Method checks, wether previous prompt was a
    *                          warning or an error. If yes the given prompt will loged by devLog
    *
    * @param    integer        $debugTrailLevel  : level for the debug trail
    * @param    string        $prompt: The prompt for devlog.
    * @return    void
    * @version       1.1.0
    * @since         1.1.0
    */
  private function timeTracking_prompt( $debugTrailLevel, $prompt )
  {
    $debugTrail = $this->drs_debugTrail( $debugTrailLevel );

    switch( true )
    {
      case( $this->tt_prevPrompt == 3 ):
        $prompt_02 = 'ERROR';
        break;
      case( $this->tt_prevPrompt == 2 ):
        $prompt_02 = 'WARN';
        break;
      default:
          // Do nothing
        return;
    }

    $prompt = 'Details about previous process: ' . $prompt . ' (' . $debugTrail['prompt'] . ')';
    t3lib_div::devLog('[INFO/PERFORMANCE] ' . $prompt, $this->extKey, $this->tt_prevPrompt );
  }

  
  
  
  /***********************************************
   *
   * Scheduler Form
   *
   **********************************************/

  /**
    * getAdditionalInformation( ) : This method returns the destination mail address as additional information
    *
    * @return string Information to display
    * @version       1.1.0
    * @since         1.1.0
    */
  public function getAdditionalInformation( )
  {    
    return $GLOBALS['LANG']->sL('LLL:EXT:postfix/lib/scheduler/locallang.xml:label.postfixAdminEmail') . ': ' . $this->postfix_postfixAdminEmail;
  }
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/postfix/lib/scheduler/class.tx_postfix_quotatask.php'])) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/postfix/lib/scheduler/class.tx_postfix_quotatask.php']);
}

?>