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

  /**
    * feusersName( )  : Returns true, if limit is overrun, false if not.
    *
    * @return boolean
    * @version       1.1.0
    * @since         1.1.0
    */
  private function feusersName( )
  {
    $username   = $this->mailboxData['username'];
    $first_name = $this->mailboxData['first_name'];
    $last_name  = $this->mailboxData['last_name'];
    
//$prompt = var_export( $this->mailboxData, true );
//t3lib_div::devlog( '[tx_postfix_QuotaTask] ' . $prompt, $this->extKey, 0 );

    switch( true )
    {
      case( $first_name ):
      case( $last_name ):
        $name = $first_name . ' ' . $last_name;
        break;
      default:
        $name = $username;
        break;
    }
    
    return $name;
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
 * @param    integer   $level      : integer
 * @return    array        $arr_return : with elements class, method, line and prompt
 * @version 1.1.0
 * @since   1.1.0
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



/**
 * drs_mailToAdmin( ): Returns class, method and line of the call of this method.
 *                    The calling method is a debug method - if it is called by another
 *                    method, please set the level in the calling method to 2.
 *
 * @param    string        $subject     : ...
 * @param    string        $body        : ...
 * @return    array        $arr_return  : with elements class, method, line and prompt
 * @version 3.9.9
 * @since   3.9.9
 */
  private function drs_mailToAdmin( $subject, $body )
  {
      // Get call method
    if( basename( PATH_thisScript ) == 'cli_dispatch.phpsh' )
    {
      $calledBy = 'CLI module dispatcher';
      $site     = '-';
    }
    else
    {
      $calledBy = 'TYPO3 backend';
      $site     = t3lib_div::getIndpEnv( 'TYPO3_SITE_URL' );
    }
      // Get call method

      // Get execution information
    $exec = $this->getExecution( );

    $start    = $exec->getStart( );
    $end      = $exec->getEnd( );
    $interval = $exec->getInterval( );
    $multiple = $exec->getMultiple( );
    $cronCmd  = $exec->getCronCmd( );
    $mailBody = $body . PHP_EOL. PHP_EOL .
      'POSTFIX QUOTA' . PHP_EOL .
      '- - - - - - - - - - - - - - - -' . PHP_EOL .
      'UID: '       . $this->taskUid . PHP_EOL .
      'Sitename: '  . $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] . PHP_EOL .
      'Site: ' . $site . PHP_EOL .
      'Called by: ' . $calledBy . PHP_EOL .
      'tstamp: ' . date( 'Y-m-d H:i:s' ) . ' [' . time( ) . ']' . PHP_EOL .
      'start: ' . date( 'Y-m-d H:i:s', $start ) . ' [' . $start . ']' . PHP_EOL .
      'end: ' . ( ( empty( $end ) ) ? '-' : ( date( 'Y-m-d H:i:s', $end ) . ' [' . $end . ']') ) . PHP_EOL .
      'interval: ' . $interval . PHP_EOL .
      'multiple: ' . ( $multiple ? 'yes' : 'no' ) . PHP_EOL .
      'cronCmd: ' . ( $cronCmd ? $cronCmd : 'not used' ) . PHP_EOL .
      '';

      // Prepare mailer and send the mail
    try 
    {
      /** @var $mailer t3lib_mail_message */
      $mailer = t3lib_div::makeInstance( 't3lib_mail_message' );
      $mailer->setFrom( array( $this->postfix_postfixAdminEmail => 'POSTFIX QUOTA' ) );
      $mailer->setReplyTo( array( $this->postfix_postfixAdminEmail => 'POSTFIX QUOTA' ) );
      $mailer->setSubject( 'POSTFIX QUOTA: ' . $subject );
      $mailer->setBody( $mailBody );
      $mailer->setTo( $this->postfix_postfixAdminEmail );
      
      $mailsSend  = $mailer->send( );
      $success    = ( $mailsSend > 0 );
    } 
    catch( Exception $e )
    {
      throw new t3lib_exception( $e->getMessage( ) );
    }

      // DRS
    if( $this->drsModeQuotaTask || $this->drsModeQuotaError )
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
      $this->quotaSet( );
      $this->quotaWarning( );
      $this->quotaRemove( );
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
    
      // RETURN : size of current mailbox is 0
    if( ! $this->mailboxSizeInBytes( ) )
    {
      return false;
    }
      // RETURN : size of current mailbox is 0
    
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
    $command  = 'du -b --max-depth=0 ' . $mailbox;
    exec( $command, $output );
      // get size of the current mailbox in bytes
    
      // RETURN : output isn't an array
    if( ! is_array( $output ) )
    {
        // DRS
      if( $this->drsModeError )
      {
        $prompt     = 'ERROR: exec doesn\'t returned an array. Command: ' . $command;
        t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
      }
        // DRS
        // Mail to admin
      $subject  = 'error in exec( )';
      $body     = 'Sorry, but the command exec( ) doesn\'t return an array. ' . PHP_EOL .
                  'Command: ' . $command . PHP_EOL .
                  'Method: ' . __METHOD__ . PHP_EOL .
                  'Line: ' . __LINE__ . PHP_EOL .
                  PHP_EOL .
                  '';
      $this->drs_mailToAdmin( $subject, $body );
        // Mail to admin
      return false;
    }
      // RETURN : output isn't an array
    
      // Get bytes and path
    $duLine = $output[0];
    list( $bytes, $path ) = explode( chr( 9 ), $duLine );
    $bytes  = ( int ) trim( $bytes );
    $path   = trim( $path );
      // Get bytes and path
    
      // RETURN : size of mailbox is 0 byte
    if( ( ( int ) $bytes ) <= 1 )
    {
        // DRS
      if( $this->drsModeError )
      {
        $prompt     = 'ERROR: size of current mailbox is 0 byte. Command: ' . $command;
        t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
      }
        // DRS
        // Mail to admin
      $subject  = 'error with size of mailbox';
      $body     = 'Sorry, but the size of the mailbox is null.' . PHP_EOL .
                  'Command: ' . $command . PHP_EOL .
                  'Method: ' . __METHOD__ . PHP_EOL .
                  'Line: ' . __LINE__ . PHP_EOL .
                  PHP_EOL .
                  '';
      $this->drs_mailToAdmin( $subject, $body );
        // Mail to admin
      return false;
    }
      // RETURN : size of mailbox is 0 byte
    
      // RETURN : size of mailbox is 0 byte
    if( $path != $mailbox )
    {
        // DRS
      if( $this->drsModeError )
      {
        $prompt     = 'ERROR: "' . $path . '" isn\'t any part of the command: ' . $command;
        t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
      }
        // DRS
        // Mail to admin
      $subject  = 'error with exec( )';
      $body     = 'Sorry, but the path "' . $path . '" isn\'t any part of the command below.' . PHP_EOL .
                  'Command: ' . $command . PHP_EOL .
                  'Method: ' . __METHOD__ . PHP_EOL .
                  'Line: ' . __LINE__ . PHP_EOL .
                  PHP_EOL .
                  '';
      $this->drs_mailToAdmin( $subject, $body );
        // Mail to admin
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

    $this->mailboxSizeInBytes = $bytes;
    return $bytes;
  }



  /***********************************************
   *
   * Quota
   *
   **********************************************/

  /**
    * quotaRemove( )  : 
    *
    * @return void
    * @version       1.1.0
    * @since         1.1.0
    */
  private function quotaRemove( )
  {
    static $bool_drsFirstLoop = true; 
    
    switch( $this->postfix_quotaMode )
    {
      case( 'remove' ):
          // Follow the workflow
        break;
      case( 'test' ):
          // DRS
        if( $bool_drsFirstLoop && $this->drsModeError )
        {
          $prompt = 'Quota remove won\'t be processed in test mode.';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 2 );
          $bool_drsFirstLoop = false;
        }
          // DRS
        return;
        break;
      case( 'warn' ):
          // DRS
        if( $bool_drsFirstLoop && $this->drsModeError )
        {
          $prompt = 'Quota remove won\'t be processed in warning mode.';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 2 );
          $bool_drsFirstLoop = false;
        }
          // DRS
        return;
        break;
      default:
          // DRS
        if( $this->drsModeError )
        {
          $prompt = 'Quota mode is undefined: "' .  $this->postfix_quotaMode . '"';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
        }
          // DRS
        return;
        break;
    }
  }

  /**
    * quotaSet( )  : 
    *
    * @return void
    * @version       1.1.0
    * @since         1.1.0
    */
  private function quotaSet( )
  {
    $quotaLimitInMegabyte = ( int ) $this->mailboxData['tx_postfix_quota'];
    if( empty ( $quotaLimitInMegabyte ) )
    {
      $quotaLimitInMegabyte = ( int ) $this->postfix_quotaLimitDefault;
    }
    
    switch( true )
    {
      case( $quotaLimitInMegabyte < 1 ):
          // DRS
        $prompt_01 = 'FATAL ERROR: current Quota limit is "' .  $quotaLimitInMegabyte . '" megabytes.';
        $prompt_02 = 'mailbox: "' .  $this->mailboxData['pathToMailbox'] . '" ';
        $prompt_03 = 'Postfix Quota will die!';
        if( $this->drsModeError )
        {
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt_01, $this->extKey, 3 );
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt_02, $this->extKey, 2 );
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt_03, $this->extKey, 3 );
        }
          // DRS
        $prompt = $prompt_01 . $prompt_02 . __METHOD__ . ' at line ' . __LINE__ . '.';
        die( $prompt );
        break;
      case( $quotaLimitInMegabyte < 50 ):
          // DRS
        if( $this->drsModeWarn )
        {
          $prompt = 'Current Quota limit is less than 50 megabytes: ' .  
                    $quotaLimitInMegabyte . ' megabytes. ' . 
                    'mailbox: ' . $this->mailboxData['pathToMailbox'] . '.';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 2 );
          $prompt = 'Please check, if this value is proper!';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 2 );
        }
        break;
      default:
          // Follow the workflow
        break;
    }
    
    $this->quotaLimitInBytes = ( int ) ( $quotaLimitInMegabyte * 1024 * 1024 );

    if( $this->drsModeQuotaTask )
    {
      $prompt = 'Quota limit in megabytes: ' . $quotaLimitInMegabyte;
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 0 );
      $prompt = 'Quota limit in bytes: ' . $this->quotaLimitInBytes;
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 0 );
    }
  }

  /**
    * quotaWarning( )  : 
    *
    * @return void
    * @version       1.1.0
    * @since         1.1.0
    */
  private function quotaWarning( )
  {
      // Get the limit for warnings in bytes
    $quotaLimitWarnInBytes = $this->quotaLimitInBytes / 100 * $this->postfix_quotaLimitWarn;
    
    if( $this->mailboxSizeInBytes <= $quotaLimitWarnInBytes )
    {
      return false;
    }

      // DRS
    if( $this->drsModeQuotaTask )
    {
      $prompt = 'Quota limit in bytes: ' . $this->quotaLimitInBytes;
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 0 );
      $prompt = 'Quota limit warn in per cent: ' . $this->postfix_quotaLimitWarn;
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 0 );
      $prompt = $this->mailboxData['pathToMailbox'] . '  overrruns the warning limit. ' .
                'Mailbox size is ' . $this->mailboxSizeInBytes . ' bytes. ' .
                'Warning limit is ' . ( $this->quotaLimitInBytes / 100 * $this->postfix_quotaLimitWarn ) . '.' .
                '';
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 2 );
    }
      // DRS
    
    $this->sendMailWarning( );
  }

  /**
    * sendMailWarning( )  : Returns true, if limit is overrun, false if not.
    *
    * @return boolean
    * @version       1.1.0
    * @since         1.1.0
    */
  private function sendMailWarning( )
  {
    $this->sendMailWarningQuotaIsOverrun( );
    $this->sendMailWarningQuotaIsNotOverrun( );
  }

  /**
    * sendMailWarningQuotaIsNotOverrun( )  : Returns true, if limit is overrun, false if not.
    *
    * @return boolean
    * @version       1.1.0
    * @since         1.1.0
    */
  private function sendMailWarningQuotaIsNotOverrun( )
  {
      // RETURN : mailbox is bigger than the quota limit
    if( $this->mailboxSizeInBytes >= $this->quotaLimitInBytes )
    {
      return;
    }
      // RETURN : mailbox is bigger than the quota limit
      
      // Get the limit for warnings in bytes
    $quotaLimitInMegabytes      = ( int ) $this->quotaLimitInBytes / 1024 / 1024;
    
      // Size of the current mailbox in megabytes
    $mailboxSizeInMegabytes     = ( int ) ( $this->mailboxSizeInBytes / 1024 / 1024 );
      // Size of the current mailbox in per cent in relation to the quota limit
    $mailboxSizeInPercent       = ( int ) ( $this->mailboxSizeInBytes / $this->quotaLimitInBytes * 100 );
    
      // Left place of the current mailbox in per cent in relation to the current quota limit
    $leftPlaceInPercent         = 100 - $mailboxSizeInPercent;

      // Size of a reduced mailbox in megabytes in relation to the current quota limit
    $reducedMailboxInMegabytes  = ( int ) ( $quotaLimitInMegabytes / 100 * $this->postfix_quotaReduceMailbox );
    
    $marker = array( );
    $marker['###FEUSERSEMAIL###']               = $this->mailboxData['email'];
    $marker['###LEFTPLACEINPERCENT###']         = $leftPlaceInPercent;
    $marker['###MAILBOXSIZEINMEGABYTES###']     = $mailboxSizeInMegabytes;
    $marker['###NAME###']                       = $this->feusersName( );
    $marker['###OVERRUNINMEGABYTES###']         = $overrunInMegabytes;
    $marker['###OVERRUNINPERCENT###']           = $overrunInPercent;
    $marker['###POSTFIXADMINCOMPANY###']        = $this->postfix_postfixAdminCompany;
    $marker['###POSTFIXADMINNAME###']           = $this->postfix_postfixAdminName;
    $marker['###POSTFIXADMINPHONE###']          = $this->postfix_postfixAdminPhone;
    $marker['###QUOTALIMITINMEGABYTES###']      = $quotaLimitInMegabytes;
    $marker['###REDUCEDMAILBOXINPERCENT###']    = $this->postfix_quotaReduceMailbox;
    $marker['###REDUCEDMAILBOXINMEGABYTES###']  = $reducedMailboxInMegabytes;
    
    $subject  = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:email.warn.overrunWarningLimit.subject' );
    $body     = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:email.warn.overrunWarningLimit.body' );
    foreach( $marker as $key => $value )
    {
      $subject  = str_replace($key, $value, $subject );
      $body     = str_replace($key, $value, $body );
    }

      // DRS
    if( $this->drsModeQuotaTask )
    {
      $prompt = $body;
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 0 );
    }
      // DRS
     
      // SWITCH : quota mode
    switch( $this->postfix_quotaMode )
    {
      case( 'warn' ):
        $body     = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.copy' ) .
                    ': ' .
                    $this->postfix_postfixAdminName . PHP_EOL .
                    PHP_EOL .
                    $body;
        $to       = 'dirk.wildt@puppenspiel-portal.eu';
        $cc       = $this->postfix_postfixAdminEmail;
        break;
      case( 'test' ):
        $subject  = '[TEST] ' . $subject;
        $body     = '[TEST] ' . PHP_EOL . 
                    PHP_EOL .
                    $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.to' ) .
                    ': ' .
                    $marker['###NAME###'] . '(' . $this->mailboxData['email'] . ')' . PHP_EOL .
                    $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.copy' ) .
                    ': ' .
                    $this->postfix_postfixAdminName . PHP_EOL .
                    PHP_EOL .
                    $body;
        $to       = $this->postfix_postfixAdminEmail;
        $cc       = null;
        break;
      case( 'remove' ):
          // DRS
        if( $this->drsModeError )
        {
          $prompt = 'Quota mode is not allowed: "' .  $this->postfix_quotaMode . '"';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
        }
          // DRS
        return false;
        break;
      default:
          // DRS
        if( $this->drsModeError )
        {
          $prompt = 'Quota mode is undefined: "' .  $this->postfix_quotaMode . '"';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
        }
          // DRS
        return false;
        break;
    }
      // SWITCH : quota mode
    
    try 
    {
      /** @var $mailer t3lib_mail_message */
      $mailer = t3lib_div::makeInstance( 't3lib_mail_message' );
      $mailer->setFrom( array( $this->postfix_postfixAdminEmail => $this->postfix_postfixAdminName ) );
      $mailer->setReplyTo( array( $this->postfix_postfixAdminEmail => $this->postfix_postfixAdminName ) );
      $mailer->setSubject( $subject );
      $mailer->setBody( $body );
      $mailer->setTo( $to );
      $mailer->setCc( $cc );
      
      $mailsSend  = $mailer->send( );
      $success    = ( $mailsSend > 0 );
    } 
    catch( Exception $e )
    {
      throw new t3lib_exception( $e->getMessage( ) );
    }

      // DRS
    if( $this->drsModeQuotaTask || $this->drsModeQuotaError )
    {
      switch( $success )
      {
        case( false ):
          $prompt = 'Undefined error. Test email couldn\'t sent to "' . $this->postfix_postfixAdminEmail . '"';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
          break;
        case( true ):
        default:
          $prompt = 'E-mail is sent to "' . $to . '"';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, -1 );
          break;
      }
    }
     // DRS
 
  }

  /**
    * sendMailWarningQuotaIsOverrun( )  : Returns true, if limit is overrun, false if not.
    *
    * @return boolean
    * @version       1.1.0
    * @since         1.1.0
    */
  private function sendMailWarningQuotaIsOverrun( )
  {
      // RETURN : mailbox is smaller than the quota limit
    if( $this->mailboxSizeInBytes <  $this->quotaLimitInBytes )
    {
      return;
    }
      // RETURN : mailbox is smaller than the quota limit
      
      // Current quota limit in megabytes
    $quotaLimitInMegabytes      = ( int ) $this->quotaLimitInBytes / 1024 / 1024;
    
      // Size of the current mailbox in megabytes
    $mailboxSizeInMegabytes     = ( int ) ( $this->mailboxSizeInBytes / 1024 / 1024 );
      // Size of the current mailbox in per cent in relation to the quota limit
    $mailboxSizeInPercent       = ( int ) ( $this->mailboxSizeInBytes / $this->quotaLimitInBytes * 100 );
    
      // Size of the overun part of the current mailbox in per cent in relation to the current quota limit
    $overrunInPercent           = $mailboxSizeInPercent - 100;
      // Size of the overun part of the current mailbox in megabytes in relation to the current quota limit
    $overrunInMegabytes         = $mailboxSizeInMegabytes - $quotaLimitInMegabytes;
    
      // Size of a reduced mailbox in megabytes in relation to the current quota limit
    $reducedMailboxInMegabytes  = ( int ) ( $quotaLimitInMegabytes / 100 * $this->postfix_quotaReduceMailbox );
    
    $marker = array( );
    $marker['###FEUSERSEMAIL###']               = $this->mailboxData['email'];
    $marker['###LEFTPLACEINPERCENT###']         = null;
    $marker['###MAILBOXSIZEINMEGABYTES###']     = $mailboxSizeInMegabytes;
    $marker['###NAME###']                       = $this->feusersName( );
    $marker['###OVERRUNINMEGABYTES###']         = $overrunInMegabytes;
    $marker['###OVERRUNINPERCENT###']           = $overrunInPercent;
    $marker['###POSTFIXADMINCOMPANY###']        = $this->postfix_postfixAdminCompany;
    $marker['###POSTFIXADMINNAME###']           = $this->postfix_postfixAdminName;
    $marker['###POSTFIXADMINPHONE###']          = $this->postfix_postfixAdminPhone;
    $marker['###QUOTALIMITINMEGABYTES###']      = $quotaLimitInMegabytes;
    $marker['###REDUCEDMAILBOXINPERCENT###']    = $this->postfix_quotaReduceMailbox;
    $marker['###REDUCEDMAILBOXINMEGABYTES###']  = $reducedMailboxInMegabytes;
      
    $subject  = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:email.warn.overrunQuotaLimit.subject' );
    $body     = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:email.warn.overrunQuotaLimit.body' );
    foreach( $marker as $key => $value )
    {
      $subject  = str_replace($key, $value, $subject );
      $body     = str_replace($key, $value, $body );
    }

      // DRS
    if( $this->drsModeQuotaTask )
    {
      $prompt = $body;
      t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 0 );
    }
      // DRS
    
      // SWITCH : quota mode
    switch( $this->postfix_quotaMode )
    {
      case( 'warn' ):
        $body     = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.copy' ) .
                    ': ' .
                    $this->postfix_postfixAdminName . PHP_EOL .
                    PHP_EOL .
                    $body;
        $to       = 'dirk.wildt@puppenspiel-portal.eu';
        $cc       = $this->postfix_postfixAdminEmail;
        break;
      case( 'test' ):
        $subject  = '[TEST] ' . $subject;
        $body     = '[TEST] ' . PHP_EOL . 
                    PHP_EOL .
                    $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.to' ) .
                    ': ' .
                    $marker['###NAME###'] . '(' . $this->mailboxData['email'] . ')' . PHP_EOL .
                    $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.copy' ) .
                    ': ' .
                    $this->postfix_postfixAdminName . PHP_EOL .
                    PHP_EOL .
                    $body;
        $to       = $this->postfix_postfixAdminEmail;
        $cc       = null;
        break;
      case( 'remove' ):
          // DRS
        if( $this->drsModeError )
        {
          $prompt = 'Quota mode is not allowed: "' .  $this->postfix_quotaMode . '"';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
        }
          // DRS
        return false;
        break;
      default:
          // DRS
        if( $this->drsModeError )
        {
          $prompt = 'Quota mode is undefined: "' .  $this->postfix_quotaMode . '"';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
        }
          // DRS
        return false;
        break;
    }
      // SWITCH : quota mode
    
    try 
    {
      /** @var $mailer t3lib_mail_message */
      $mailer = t3lib_div::makeInstance( 't3lib_mail_message' );
      $mailer->setFrom( array( $this->postfix_postfixAdminEmail => $this->postfix_postfixAdminName ) );
      $mailer->setReplyTo( array( $this->postfix_postfixAdminEmail => $this->postfix_postfixAdminName ) );
      $mailer->setSubject( $subject );
      $mailer->setBody( $body );
      $mailer->setTo( $to );
      $mailer->setCc( $cc );
      
      $mailsSend  = $mailer->send( );
      $success    = ( $mailsSend > 0 );
    } 
    catch( Exception $e )
    {
      throw new t3lib_exception( $e->getMessage( ) );
    }

      // DRS
    if( $this->drsModeQuotaTask || $this->drsModeQuotaError )
    {
      switch( $success )
      {
        case( false ):
          $prompt = 'Undefined error. Test email couldn\'t sent to "' . $this->postfix_postfixAdminEmail . '"';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, 3 );
          break;
        case( true ):
        default:
          $prompt = 'E-mail is sent to "' . $to . '"';
          t3lib_div::devLog( '[tx_postfix_QuotaTask]: ' . $prompt, $this->extKey, -1 );
          break;
      }
    }
     // DRS
 
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
    $postfixAdminEmail  = 'Admin' . 
                          ': ' .
                          $this->postfix_postfixAdminEmail . 
                          '. ';
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
                          $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaMode.' . $this->postfix_quotaMode ) .
                          '. ';
    $quotaLimitDefault   = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaLimitDefault' ) .
                          ': ' . 
                          $this->postfix_quotaLimitDefault . 
                          '. ';
    $quotaLimitRemove   = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaLimitRemove' ) .
                          ': ' . 
                          $this->postfix_quotaLimitRemove .
                          '. ';
    $quotaLimitWarn     = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaLimitWarn' ) .
                          ': ' . 
                          $this->postfix_quotaLimitWarn .
                          '. ';
    $quotaReduceMailbox   = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaReduceMailbox' ) .
                          ': ' . 
                          $this->postfix_quotaReduceMailbox .
                          '. ';
    return $quotaMode . $postfixAdminEmail. $quotaLimitDefault . $quotaLimitRemove . $quotaLimitWarn . $quotaReduceMailbox;
  }
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/postfix/lib/scheduler/class.tx_postfix_quotatask.php'])) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/postfix/lib/scheduler/class.tx_postfix_quotatask.php']);
}

?>