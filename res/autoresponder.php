<?php
  /*
      goldfish - the PHP auto responder for postfix
      Copyright (C) 2007-2008 by Remo Fritzsche
  
      This program is free software: you can redistribute it and/or modify
      it under the terms of the GNU General Public License as published by
      the Free Software Foundation, either version 3 of the License, or
      any later version.
  
      This program is distributed in the hope that it will be useful,
      but WITHOUT ANY WARRANTY; without even the implied warranty of
      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
      GNU General Public License for more details.
  
      You should have received a copy of the GNU General Public License
      along with this program.  If not, see <http://www.gnu.org/licenses/>.
      
      (c)      2010 Dirk Wildt      (Extended and adapted to TYPO3)
                                    (Extended with prevention for autoreply loops)
      (c) 2007-2009 Remo Fritzsche  (Main application programmer)
      (c)      2009 Karl Herrick    (Bugfix)
      (c) 2007-2008 Manuel Aller    (Additional programming)
      
      Version 1.0-STABLE
  */
  
  ini_set('display_errors', true);
  error_reporting( E_ALL );
  
  ######################################
  # Check PHP version                  #
  ######################################
  
  if ( version_compare( PHP_VERSION, '5.0.0' ) == - 1 )
  {
    echo 'Error, you are currently not running PHP 5 or later. Exiting.'."\n";
    exit;
  }
    
  ######################################
  # Configuration                      #
  ######################################
  /* General */
  $conf['cycle'] = 5 * 60;
  
  /* Logging */
  // dwildt, 100818
//    $conf['log_file_path'] = '/var/log/goldfish';
  $conf['log_file_path'] = '/home/log/goldfish';
  $conf['write_log'] = true;
  
  /* Database information */
  // dwildt, 100818
//    $conf['mysql_host'] = 'localhost';
//    $conf['mysql_user'] = 'myuser';
//    $conf['mysql_password'] = 'mypassword';
//    $conf['mysql_database'] = 'mailserver';
  $conf['mysql_host']     = 'localhost';
  $conf['mysql_user']     = 'postfix';
  $conf['mysql_password'] = 'Trag*Koen';
  $conf['mysql_database'] = 'typo3_40';
  
  /* Database Queries */
  
  # This query has to return the path (`path`) of the corresponding
  # maildir-Mailbox with email-address %m
  // dwildt, 100818
//    $conf['q_mailbox_path'] = 'SELECT CONCAT('/home/vmail/', SUBSTRING_INDEX(email,'@',-1), '/', SUBSTRING_INDEX(email,'@',1), '/') as `path` FROM view_users WHERE `email` = '%m'';
  $conf['q_mailbox_path'] = 'SELECT CONCAT(tx_postfix_homedir, \'/\', tx_postfix_maildir) as `path` FROM fe_users WHERE `email` = \'%m\'';

  # This query has to return the following fields from the autoresponder table: `from`, `to`, `email`, `message` where `enabled` = 2
  // dwildt, 100818: Change for roundcube plugin
  //$conf['q_forwardings'] = 'SELECT * FROM `tx_postfix_autoresponder` WHERE `enabled` = 1 AND `force_enabled` = 1';
  // Bugfix #9417, dwildt, 100823
  $conf['q_forwardings'] = 'SELECT * FROM `tx_postfix_autoresponder` WHERE `force_enabled` = 1 AND ((`from_date` <= CURDATE() AND `to_date` >= CURDATE()))'; 
  
  # This query has to disable every autoresponder entry which ended in the past
  // Bugfix #9417, dwildt, 100823
//  $conf['q_disable_forwarding'] = 'UPDATE `tx_postfix_autoresponder` SET `enabled` = 0 WHERE `to_date` < CURDATE();';
  
  # This query has to activate every autoresponder entry which starts today
  // dwildt, 100818: Change for roundcube plugin
  // Bugfix #9417, dwildt, 100823
//  $conf['q_enable_forwarding'] = 'UPDATE `tx_postfix_autoresponder` SET `enabled` = 1 WHERE `from_date` <= CURDATE();';
  
  # This query has to return the message of an autoresponder entry identified by email %m
  $conf['q_messages'] = 'SELECT `message` FROM `tx_postfix_autoresponder` WHERE `email` = \'%m\'';
  
  # This query has to return the subject of the autoresponder entry identified by email %m
  $conf['q_subject'] = 'SELECT `subject` FROM `tx_postfix_autoresponder` WHERE `email` = \'%m\'';
  
  ######################################
  # Logger class                       #
  ######################################
  
  class Logger
  {
  var $logfile;
  var $str;

  function addLine($str)
  {
    // dwildt, 100819
    //$str = date('Y-m-d h:i:s').' '.$str;
    $str = date('Y-m-d H:i:s').' '.$str;
    $this->str .= "\n".'$str';
    echo $str."\n";
  }

  function writeLog(&$conf)
  {
      if (! $conf['write_log'] ) return;
      
      if (is_writable($conf['log_file_path']))
      {
        $this->addLine('--------- End execution ------------');
        if (!$handle = fopen($conf['log_file_path'], 'a'))
        {
          echo 'Cannot open file ({'.$conf['log_file_path'].'})';
          exit;
        }

        if (fwrite($handle, $this->str) === FALSE)
        {
          echo 'Cannot write to file)';
          exit;
        }
        else
        {
          echo 'Wrote log successfully.';
        }

        fclose($handle);
      }
      else
      {
        echo 'Error: The log file is not writeable.'."\n";
        echo 'The log has not been written.'."\n";
      }
    }
  }
  
  ######################################
  # Create log object                  #
  ######################################
  $log = new Logger();
  
  ######################################
  # function endup()                   #
  ######################################
  function endup(&$log, &$conf)
  {
    $log->writeLog($conf);
    exit;
  }
  
  ######################################
  # Database connection                #
  ######################################
  $link = @mysql_connect($conf['mysql_host'], $conf['mysql_user'], $conf['mysql_password']);
  if (!$link)
  {
    $log->addLine('Could not connect to database. Abborting.');
    endup($log, $conf);
  }
  else
  {
    $log->addLine('Connection to database established successfully');
  
    if (!mysql_select_db($conf['mysql_database']))
    {
      $log->addLine('Could not select database '.$conf['mysql_database']);
      endup($log, $conf);
    }
    else
    {
      $log->addLine('Database selected successfully');
    }
  }
  
  ######################################
  # Update database entries            #
  ######################################
// Bugfix #9417, dwildt, 100823
//  $result = mysql_query($conf['q_disable_forwarding']);
//  
//  if (!$result)
//  {
//    $log->addLine('Error in query \'q_disable_forwarding\''.$conf['q_disable_forwarding']."\n".mysql_error());
//  }
//  else
//  {
//    $log->addLine('Successfully updated database (disabled entries)');
//  }
//  
//  mysql_query($conf['q_enable_forwarding']);
//  
//  if (!$result)
//  {
//    $log->addLine('Error in query \'q_enable_forwarding\''.$conf['q_enable_forwarding']."\n".mysql_error());
//  }
//  else
//  {
//    $log->addLine('Successfully updated database (enabled entries)');
//  }
  
  ######################################
  # Catching dirs of autoresponders mailboxes #
  ######################################
  
  // Corresponding email addresses
  $result = mysql_query($conf['q_forwardings']);
  
  if (!$result)
  {
    $log->addLine('Error in query \'q_forwardings\''.$conf['q_forwardings']."\n".mysql_error());
    exit;
  }
 
  $num = mysql_num_rows($result);
  
  for ($i = 0; $i < $num; $i++)
  {
    $emails[] = mysql_result($result, $i, 'email');
    $name[]   = mysql_result($result, $i, 'descname');
  }
  
  // Fetching directories
  for ($i = 0; $i < $num; $i++)
  {
    // dwildt, 100818
    $query  = str_replace('%m', $emails[$i], $conf['q_mailbox_path']);
    $result = mysql_query($query);
    if (!$result)
    {
      $log->addLine('Error in query '.$query."\n".mysql_error()."\n"); 
      $log->addLine('email: '.$emails[$i]."\n"); 
      $log->addLine('path:  '.$conf['q_mailbox_path']."\n"); 
      exit; 
    }
    else
    {
      $log->addLine('Successfully fetched maildir directories'); 
    }
    $paths[] = mysql_result($result, 0, 'path') . 'new/';
  }
  
  ######################################
  # Reading new mails                  #
  ######################################
  if ($num > 0)
  {
    $i = 0;
    
    foreach ($paths as $path)
    {
      foreach(scandir($path) as $entry)
      {
        if ($entry != '.' && $entry != '..')
        {
          if (time() - filemtime($path . $entry) - $conf['cycle'] <= 0)
          {
            $mails[] = $path . $entry;
            
            ###################################
            # Send response                   #
            ###################################
            
            // Reading mail address
            $mail = file($path.$entry);
            
            // dwildt, 100819
            $bool_doAutoreply = true;
            $sender_subject   = null;
            
            foreach ($mail as $line) 
            {
              $line = trim($line); 
              
              if (substr($line, 0, 12) == 'Return-Path:') 
              { 
                $returnpath = substr($line, strpos($line, '<') + 1, strpos($line, '>') - strpos($line, '<')-1)."\n"; 
              } 
              if (substr($line, 0, 5) == 'From:' && strstr($line,'@')) 
              { 
                $address = substr($line, strpos($line, '<') + 1, strpos($line, '>') - strpos($line, '<')-1)."\n"; 
                // dwildt, 100819
                //break; 
              } 
              elseif(substr($line,0,5) == 'From:' && !strstr($line,'@') && !empty ($returnpath)) 
              { 
                $address = $returnpath; 
                // dwildt, 100819
                //break; 
              } 
              // dwildt, 100819
              // Get the subject for a optional '/subject/' replace in the autoreply subject and body
              $key = 'Subject: ';
              if (substr($line, 0, strlen($key)) == $key)
              { 
                $sender_subject = substr($line, strlen($key));
              }

              // dwildt, 100819
              // Prevent the autoreply for autoreply mails
              $key = 'Auto-Submitted: ';
              if (substr($line, 0, strlen($key)) == $key)
              {
                $value = substr($line, strlen($key));
                switch(strtolower($value))
                {
                  case('auto-generated'):
                  case('auto-replied'):
                  case('auto-forwarded'):
                    $bool_doAutoreply = false;
                    $log->addLine('No autoreply because of header: '.$line);
                    break;
                  case('no'):
                  default:
                    // It isn't an autoreply mail
                }
              }
              $key = 'X-Autoresponse: ';
              if (substr($line, 0, strlen($key)) == $key)
              { 
                $value = substr($line, strlen($key));
                if(strtolower($value) == 'yes')
                {
                  $bool_doAutoreply = false;
                  $log->addLine('No autoreply because of header: '.$line);
                  break;
                }
              }
              // Prevent the autoreply for autoreply mails
            } 

            // Check: Is this mail allready answered
            if (empty($address))
            {
              // dwildt, 100819
              $bool_doAutoreply = false;
              $log->addLine('Error, could not parse mail $path');
            }

            if ($bool_doAutoreply)
            {
              // Get data of current mail
              $email = $emails[$i];

              // Get subject
              $result = mysql_query(str_replace('%m', $emails[$i], $conf['q_subject']));
          
              if (!$result)
              {
                $log->addLine('Error in query '.$conf['q_subject']."\n".mysql_error()); exit;
              }
              else
              {
                $log->addLine('Successfully fetched subject of {'.$emails[$i].'}');
              }
  
              $subject = mysql_result($result, 0, 'subject');
              $subject_wo_placeholder = str_replace('/subject/', '', $subject);
              $subject = str_replace('/subject/', $sender_subject, $subject);
//:TODO:
              // dwildt, 100819
              // Prevent the autoreply for autoreply mails
              $pos = strpos($sender_subject, $subject_wo_placeholder);
              if(!($pos === false))
              {
                $bool_doAutoreply = false;
                $log->addLine('No autoreply: Subject is part of sended subject.');
                $log->addLine('Subject without placeholder : '.$subject_wo_placeholder);
                $log->addLine('Sended Subject              : '.$sender_subject);
              }
              // Prevent the autoreply for autoreply mails

              if($bool_doAutoreply)
              {
                // Get Message
                $result = mysql_query(str_replace('%m', $emails[$i], $conf['q_messages']));
                
                if (!$result)
                {
                  $log->addLine('Error in query '.$conf['q_messages']."\n".mysql_error()); exit;
                }
                else
                {
                  $log->addLine('Successfully fetched message of {'.$emails[$i].'}');
                }
            
                $message = mysql_result($result, 0, 'message');
                // dwildt, 100819
                $message = str_replace('/subject/', $sender_subject, $message);
                $headers = 'From: '.$name[$i].'<'.$emails[$i].'>';
  
                // dwildt, 100819
                // @ http://tools.ietf.org/html/draft-palme-autosub-01
                $headers = $headers."\n".'MIME-Version: 1.0';
                $headers = $headers."\n".'Content-Type: text/plain; charset=iso-8859-15';
                $headers = $headers."\n".'Auto-Submitted: auto-replied';
                $headers = $headers."\n".'X-Autoresponse: yes';
  
                // Check if mail is allready an answer:
                // dwildt, 100819
                // if (strstr((string) $mail, $message))
                if (strstr((string) $mail, $message))
                {
                  $log->addLine('Mail from {'.$emails[$i].'} allready answered');
                  break;
                }
  
                // strip the line break from $address for checks
                // fix by Karl Herrick, thank's a lot
                if ( substr($address,0,strlen($address)-1) == $email )
                {
                  $log->addLine('Email address from autoresponder table is the same as the intended recipient! Not sending the mail!');
                  break;
                }
                mail($address, $subject, $message, $headers);
                $log->addLine('\''.$subject.'\': '.$emails[$i].' -> '.$address);
              }
            }
          }
        }
      }
      $i++;
    }
  }

  $log->writeLog($conf);
  echo 'End execution.';
?>