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
 */
class tx_postfix_QuotaTask extends tx_scheduler_Task {

    /**
     * An email address to be used during the process
     *
     * @var string $email
     */
     var $postfix_postfixAdminEmail;

    /**
     * Function executed from the Scheduler.
     * Sends an email
     *
     * @return boolean
     */
    public function execute() {
        $success = FALSE;

        if (!empty($this->postfix_postfixAdminEmail)) {
                // If an email address is defined, send a message to it

                // NOTE: the TYPO3_DLOG constant is not used in this case, as this is a test task
                // and debugging is its main purpose anyway
            t3lib_div::devLog('[tx_postfix_QuotaTask]: Test email sent to "' . $this->postfix_postfixAdminEmail . '"', 'postfix', 0);

                // Get execution information
            $exec = $this->getExecution();

                // Get call method
            if (basename(PATH_thisScript) == 'cli_dispatch.phpsh') {
                $calledBy = 'CLI module dispatcher';
                $site = '-';
            } else {
                $calledBy = 'TYPO3 backend';
                $site = t3lib_div::getIndpEnv('TYPO3_SITE_URL');
            }

            $start    = $exec->getStart();
            $end      = $exec->getEnd();
            $interval = $exec->getInterval();
            $multiple = $exec->getMultiple();
            $cronCmd  = $exec->getCronCmd();
            $mailBody =
                'POSTFIX QUOTA' . LF
                . '- - - - - - - - - - - - - - - -' . LF
                . 'UID: ' . $this->taskUid . LF
                . 'Sitename: ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] . LF
                . 'Site: ' . $site . LF
                . 'Called by: ' . $calledBy . LF
                . 'tstamp: ' . date('Y-m-d H:i:s') . ' [' . time() . ']' . LF
                . 'start: ' . date('Y-m-d H:i:s', $start) . ' [' . $start . ']' . LF
                . 'end: ' . ((empty($end)) ? '-' : (date('Y-m-d H:i:s', $end) . ' [' . $end . ']')) . LF
                . 'interval: ' . $interval . LF
                . 'multiple: ' . ($multiple ? 'yes' : 'no') . LF
                . 'cronCmd: ' . ($cronCmd ? $cronCmd : 'not used');

                // Prepare mailer and send the mail
            try {
                /** @var $mailer t3lib_mail_message */
                $mailer = t3lib_div::makeInstance('t3lib_mail_message');
                $mailer->setFrom(array($this->postfix_postfixAdminEmail => 'POSTFIX QUOTA'));
                $mailer->setReplyTo(array($this->postfix_postfixAdminEmail => 'POSTFIX QUOTA'));
                $mailer->setSubject('POSTFIX QUOTA');
                $mailer->setBody($mailBody);
                $mailer->setTo($this->postfix_postfixAdminEmail);
                $mailsSend = $mailer->send();
                $success = ($mailsSend>0);
            } catch (Exception $e) {
                throw new t3lib_exception($e->getMessage());
            }
        } else {
                // No email defined, just log the task
            t3lib_div::devLog('[tx_postfix_QuotaTask]: No email address given', 'postfix', 2);
        }

        return $success;
    }

    /**
     * This method returns the destination mail address as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation() {
        return $GLOBALS['LANG']->sL('LLL:EXT:postfix/lib/scheduler/locallang.xml:label.postfixAdminEmail') . ': ' . $this->postfix_postfixAdminEmail;
    }
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/postfix/lib/scheduler/class.tx_postfix_quotatask.php'])) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/postfix/lib/scheduler/class.tx_postfix_quotatask.php']);
}

?>