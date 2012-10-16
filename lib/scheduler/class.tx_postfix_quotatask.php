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
 * Class "tx_postfix_QuotaTask" provides procedures for check quota and control postfix mailboxes
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
    * All Postfix mailboxes returned from database
    *
    * @var array $mailboxesData
    */
    var $mailboxesData;
    

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

      // 
    if( ! $this->initRequirements( ) )
    {
      $success = false;
      return $success;
    }
    
      // Init timetracking, set the starttime
    $this->timeTracking_init( );
    $debugTrailLevel = 1;
    $this->timeTracking_log( $debugTrailLevel, 'START' );

      // Get the data of each postfix mailbox from the database
    $this->mailboxesData = $this->initMailboxesData( );
    
      // Loop all mailboxes;
    if( ! $this->mailboxes( ) )
    {
      $success = false;
    }
      // Loop all mailboxes;
    
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
    * initMailboxesData( ) : Get the data of each postfix mailbox from the database.
    *
    * @return   array   $rows
    * @version       1.1.0
    * @since         1.1.0
    */
  private function initMailboxesData( )
  {
      // Query
    $select_fields  = "
                        uid, 
                        username, 
                        first_name, 
                        last_name, 
                        email,
                        CONCAT( tx_postfix_homedir, '/', tx_postfix_maildir ) AS 'pathToMailbox', 
                        tx_postfix_quota
                      ";
    $from_table     = "fe_users";
    $where_clause   = "(tx_postfix_homedir != '' OR tx_postfix_maildir != '')";
    $groupBy        = null;
    $orderBy        = "pathToMailbox";
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
      $prompt = '#' . count( $rows ) . ' mailboxes found.';
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
    * initRequirements( ) : 
    *
    * @return   boolean   
    * @version       1.1.0
    * @since         1.1.0
    */
  private function initRequirements( )
  {
      // SWITCH : server OS
    switch( strtolower( PHP_OS ) )
    {
      case( 'linux' ):
          // Linux is proper: Follow the workflow
        break;
      default:
          // RETURN : OS isn't supported
          // DRS
        if( $this->drsModeError )
        {
          $prompt = 'Sorry, but the operating system "' . PHP_OS . '" isn\'t supported by TYPO3 Postfix.';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
        }
          // DRS
        return false;
          // RETURN : OS isn't supported
    }
      // SWITCH : server OS
      
      // RETURN : email address is given
    if ( ! empty( $this->postfix_postfixAdminEmail ) ) 
    {
      return true;
    }
      // RETURN : email address is given

      // DRS
    if( $this->drsModeError )
    {
      $prompt = 'email address is missing for the Postfix admin.';
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
    }
      // DRS
    
    

    return false;
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
  private function drs_debugTrail( $level = 1 )
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
   * Mailboxes
   *
   **********************************************/

  /**
    * mailboxes( )  : 
    *
    * @return boolean
    * @version       1.1.0
    * @since         1.1.0
    */
  private function mailboxes( )
  {
    $success = true;
    
    foreach( $this->mailboxesData as $this->mailboxData )
    {
      if( ! $this->mailbox( ) )
      {
        // ...
        continue;
      }
    }

    return $success;
  }

  /**
    * mailbox( )  : 
    *
    * @return boolean
    * @version       1.1.0
    * @since         1.1.0
    */
  private function mailbox( )
  {
    $success = true;
    
      // Get size of the current mailbox
    $mailboxSizeInBytes = $this->mailboxSizeInBytes( );
    
      // RETURN : size is 0
    if( empty( $mailboxSizeInBytes ) )
    {
      return false;
    }
      // RETURN : size is 0

    return $success;

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

    $strMailboxData = var_export( $this->mailboxesData, true );

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
      $strMailboxData . PHP_EOL .
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

  /**
    * mailboxSizeInBytes( )  : 
    *
    * @return boolean
    * @version       1.1.0
    * @since         1.1.0
    */
  private function mailboxSizeInBytes( )
  {
      // get size of the current mailbox in bytes
    $output   = null;
    $mailbox  = $this->mailboxData['pathToMailbox'];
    $command  = 'du --max-depth=0 ' . $mailbox;
    exec( $command, $output );
      // get size of the current mailbox in bytes
    
    if( $this->drsModeQuotaTask )
    {
      $prompt = var_export( $output, true );
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 0 );
    }
    
      // RETURN : output isn't an array
    if( ! is_array( $output ) )
    {
      if( $this->drsModeError )
      {
        $prompt     = 'ERROR: exec doesn\'t returned an array. Command: ' . $command;
        t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
      }
      return false;
    }
      // RETURN : output isn't an array
    
      // Get bytes and path
    $duLine = $output[0];
    list( $bytes, $path ) = explode( ' ', $duLine );
    $bytes  = trim( $bytes );
    $path   = trim( $path );
    if( $this->drsModeError )
    {
      $prompt     = 'duLine: ' . $duLine;
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
      $prompt     = 'explode( " ", $duLine ): ' . var_export( explode( ' ', $duLine ), true );
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
      $prompt     = 'bytes: ' . $bytes;
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
      $prompt     = '( int ) bytes: ' . ( int ) $bytes;
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
      $prompt     = 'path: ' . $path;
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
    }
      // Get bytes and path
    
      // RETURN : size of mailbox is 0 byte
    if( ( ( int ) $bytes ) <= 1 )
    {
      if( $this->drsModeError )
      {
        $prompt     = 'ERROR: size of current mailbox is 0 byte. Command: ' . $command;
        t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
      }
      return false;
    }
      // RETURN : size of mailbox is 0 byte
    
      // RETURN : size of mailbox is 0 byte
    if( $path != $mailbox )
    {
      if( $this->drsModeError )
      {
        $prompt     = 'ERROR: ' . $path . ' isn\'t any part of the command: ' . $command;
        t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
      }
      return false;
    }
      // RETURN : size of mailbox is 0 byte
    
      // DRS
    if( $this->drsModeQuotaTask )
    {
      $prompt = $mailbox . ': ' . $bytes . ' bytes';
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 0 );
    }
      // DRS

    return $bytes;
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
    $postfixAdminEmail  = $this->postfix_postfixAdminEmail;
// Kein Effekt
//    $quotaMode          = htmlspecialchars_decode( $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaMode' ) ) .
//                          ': ' . 
//                          htmlspecialchars_decode( $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaMode.' . $this->postfix_quotaMode ) );
// &ouml; wird Ä   
//    $quotaMode          = html_entity_decode( $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaMode' ) ) .
//                          ': ' . 
//                          html_entity_decode( $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaMode.' . $this->postfix_quotaMode ) );
    $quotaMode          = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaMode' ) .
                          ': ' . 
                          $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaMode.' . $this->postfix_quotaMode );
    return $quotaMode . ' (admin e-mail: ' . $postfixAdminEmail . ')'; //x
  }
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/postfix/lib/scheduler/class.tx_postfix_quotatask.php'])) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/postfix/lib/scheduler/class.tx_postfix_quotatask.php']);
}

?>