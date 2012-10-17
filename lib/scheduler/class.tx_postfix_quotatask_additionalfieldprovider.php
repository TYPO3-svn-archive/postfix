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
 * Aditional fields provider class for usage with the postfix quota task
 *
 * @author        Dirk Wildt (http://wildt.at.die-netzmacher.de/)
 * @package        TYPO3
 * @subpackage    postfix
 * @version       1.1.0
 * @since         1.1.0
 */
class tx_postfix_QuotaTask_AdditionalFieldProvider implements tx_scheduler_AdditionalFieldProvider
{
  
  var $msgPrefix = 'Postfix Quota';

  /**
    * getAdditionalFields( )  : This method is used to define new fields for adding or editing a task
    *                           In this case, it adds an email field
    *
    * @param array $taskInfo Reference to the array containing the info used in the add/edit form
    * @param object $task When editing, reference to the current task object. Null when adding.
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return array    Array containing all the information pertaining to the additional fields
    *                    The array is multidimensional, keyed to the task class name and each field's id
    *                    For each field it provides an associative sub-array with the following:
    *                        ['code']        => The HTML code for the field
    *                        ['label']        => The label of the field (possibly localized)
    *                        ['cshKey']        => The CSH key for the field
    *                        ['cshLabel']    => The code of the CSH label
    * @version       1.1.0
    * @since         1.1.0
    */
  public function getAdditionalFields( array &$taskInfo, $task, tx_scheduler_Module $parentObject ) 
  {
    $additionalFields = array( );
    $additionalFields = $additionalFields + $this->getFieldPostfixAdminEmail( $taskInfo, $task, $parentObject );
    $additionalFields = $additionalFields + $this->getFieldPathToFolderWiDrafts( $taskInfo, $task, $parentObject );
    $additionalFields = $additionalFields + $this->getFieldQuotaMode( $taskInfo, $task, $parentObject );
    $additionalFields = $additionalFields + $this->getFieldQuotaLimitDefault( $taskInfo, $task, $parentObject );
    $additionalFields = $additionalFields + $this->getFieldQuotaLimitRemove( $taskInfo, $task, $parentObject );
    $additionalFields = $additionalFields + $this->getFieldQuotaLimitWarn( $taskInfo, $task, $parentObject );
    $additionalFields = $additionalFields + $this->getFieldQuotaReduceMailbox( $taskInfo, $task, $parentObject );
//    quotaDefaultLimit
    
    return $additionalFields;
  }

  /**
    * getFieldQuotaReduceMailbox( )  : This method is used to define new fields for adding or editing a task
    *                                           In this case, it adds an email field
    *
    * @param array $taskInfo Reference to the array containing the info used in the add/edit form
    * @param object $task When editing, reference to the current task object. Null when adding.
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return array    Array containing all the information pertaining to the additional fields
    *                    The array is multidimensional, keyed to the task class name and each field's id
    *                    For each field it provides an associative sub-array with the following:
    *                        ['code']        => The HTML code for the field
    *                        ['label']        => The label of the field (possibly localized)
    *                        ['cshKey']        => The CSH key for the field
    *                        ['cshLabel']    => The code of the CSH label
    * @version       1.1.0
    * @since         1.1.0
    */
  private function getFieldQuotaReduceMailbox( array &$taskInfo, $task, $parentObject ) 
  {
      // IF : field is empty, initialize extra field value
    if( empty( $taskInfo['postfix_quotaReduceMailbox'] ) ) 
    {
      if( $parentObject->CMD == 'add' ) 
      {
          // In case of new task and if field is empty, set default email address
        $taskInfo['postfix_quotaReduceMailbox'] = '75';
      } 
      elseif( $parentObject->CMD == 'edit' ) 
      {
          // In case of edit, and editing a test task, set to internal value if not data was submitted already
        $taskInfo['postfix_quotaReduceMailbox'] = $task->postfix_quotaReduceMailbox;
      }
      else
      {
          // Otherwise set an empty value, as it will not be used anyway
        $taskInfo['postfix_quotaReduceMailbox'] = '';
      }
    }
      // IF : field is empty, initialize extra field value

      // Write the code for the field
    $fieldID    = 'postfix_quotaReduceMailbox';
    $fieldValue = htmlspecialchars( $taskInfo['postfix_quotaReduceMailbox'] );
    $fieldCode  = '<input type="text" name="tx_scheduler[postfix_quotaReduceMailbox]" id="' . $fieldID . '" value="' . $fieldValue . '" size="3"  maxlength="3"/>';
    $additionalFields = array( );
    $additionalFields[$fieldID] = array
    (
      'code'     => $fieldCode,
      'label'    => 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaReduceMailbox',
      'cshKey'   => '_MOD_tools_txschedulerM1',
      'cshLabel' => $fieldID
    );
      // Write the code for the field

    return $additionalFields;
  }

  /**
    * getFieldPathToFolder( )  : This method is used to define new fields for adding or editing a task
    *                                           In this case, it adds an email field
    *
    * @param array $taskInfo Reference to the array containing the info used in the add/edit form
    * @param object $task When editing, reference to the current task object. Null when adding.
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return array    Array containing all the information pertaining to the additional fields
    *                    The array is multidimensional, keyed to the task class name and each field's id
    *                    For each field it provides an associative sub-array with the following:
    *                        ['code']        => The HTML code for the field
    *                        ['label']        => The label of the field (possibly localized)
    *                        ['cshKey']        => The CSH key for the field
    *                        ['cshLabel']    => The code of the CSH label
    * @version       1.1.0
    * @since         1.1.0
    */
  private function getFieldPathToFolderWiDrafts( array &$taskInfo, $task, $parentObject ) 
  {
      // IF : field is empty, initialize extra field value
    if( empty( $taskInfo['postfix_pathToFolderWiDrafts'] ) ) 
    {
      if( $parentObject->CMD == 'add' ) 
      {
          // In case of new task and if field is empty, set default email address
        $taskInfo['postfix_pathToFolderWiDrafts'] = 'typo3conf/ext/postfix/lib/scheduler/maildrafts/';
      } 
      elseif( $parentObject->CMD == 'edit' ) 
      {
          // In case of edit, and editing a test task, set to internal value if not data was submitted already
        $taskInfo['postfix_pathToFolderWiDrafts'] = $task->postfix_pathToFolderWiDrafts;
      }
      else
      {
          // Otherwise set an empty value, as it will not be used anyway
        $taskInfo['postfix_pathToFolderWiDrafts'] = '';
      }
    }
      // IF : field is empty, initialize extra field value

      // Write the code for the field
    $fieldID    = 'postfix_pathToFolderWiDrafts';
    $fieldValue = htmlspecialchars( $taskInfo['postfix_pathToFolderWiDrafts'] );
    $fieldCode  = '<input type="text" name="tx_scheduler[postfix_pathToFolderWiDrafts]" id="' . $fieldID . '" value="' . $fieldValue . '" size="50" />';
    $additionalFields = array( );
    $additionalFields[$fieldID] = array
    (
      'code'     => $fieldCode,
      'label'    => 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.pathToFolderWiDrafts',
      'cshKey'   => '_MOD_tools_txschedulerM1',
      'cshLabel' => $fieldID
    );
      // Write the code for the field

    return $additionalFields;
  }


  /**
    * getFieldPostfixAdminEmail( )  : This method is used to define new fields for adding or editing a task
    *                                           In this case, it adds an email field
    *
    * @param array $taskInfo Reference to the array containing the info used in the add/edit form
    * @param object $task When editing, reference to the current task object. Null when adding.
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return array    Array containing all the information pertaining to the additional fields
    *                    The array is multidimensional, keyed to the task class name and each field's id
    *                    For each field it provides an associative sub-array with the following:
    *                        ['code']        => The HTML code for the field
    *                        ['label']        => The label of the field (possibly localized)
    *                        ['cshKey']        => The CSH key for the field
    *                        ['cshLabel']    => The code of the CSH label
    * @version       1.1.0
    * @since         1.1.0
    */
  private function getFieldPostfixAdminEmail( array &$taskInfo, $task, $parentObject ) 
  {
      // IF : field is empty, initialize extra field value
    if( empty( $taskInfo['postfix_postfixAdminEmail'] ) ) 
    {
      if( $parentObject->CMD == 'add' ) 
      {
          // In case of new task and if field is empty, set default email address
        $taskInfo['postfix_postfixAdminEmail'] = $GLOBALS['BE_USER']->user['email'];
      } 
      elseif( $parentObject->CMD == 'edit' ) 
      {
          // In case of edit, and editing a test task, set to internal value if not data was submitted already
        $taskInfo['postfix_postfixAdminEmail'] = $task->postfix_postfixAdminEmail;
      }
      else
      {
          // Otherwise set an empty value, as it will not be used anyway
        $taskInfo['postfix_postfixAdminEmail'] = '';
      }
    }
      // IF : field is empty, initialize extra field value

      // Write the code for the field
    $fieldID    = 'postfix_postfixAdminEmail';
    $fieldValue = htmlspecialchars( $taskInfo['postfix_postfixAdminEmail'] );
    $fieldCode  = '<input type="text" name="tx_scheduler[postfix_postfixAdminEmail]" id="' . $fieldID . '" value="' . $fieldValue . '" size="50" />';
    $additionalFields = array( );
    $additionalFields[$fieldID] = array
    (
      'code'     => $fieldCode,
      'label'    => 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.postfixAdminEmail',
      'cshKey'   => '_MOD_tools_txschedulerM1',
      'cshLabel' => $fieldID
    );
      // Write the code for the field

    return $additionalFields;
  }

  /**
    * getFieldQuotaLimitDefault( )  : This method is used to define new fields for adding or editing a task
    *                                           In this case, it adds an email field
    *
    * @param array $taskInfo Reference to the array containing the info used in the add/edit form
    * @param object $task When editing, reference to the current task object. Null when adding.
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return array    Array containing all the information pertaining to the additional fields
    *                    The array is multidimensional, keyed to the task class name and each field's id
    *                    For each field it provides an associative sub-array with the following:
    *                        ['code']        => The HTML code for the field
    *                        ['label']        => The label of the field (possibly localized)
    *                        ['cshKey']        => The CSH key for the field
    *                        ['cshLabel']    => The code of the CSH label
    * @version       1.1.0
    * @since         1.1.0
    */
  private function getFieldQuotaLimitDefault( array &$taskInfo, $task, $parentObject ) 
  {
      // IF : field is empty, initialize extra field value
    if( empty( $taskInfo['postfix_quotaLimitDefault'] ) ) 
    {
      if( $parentObject->CMD == 'add' ) 
      {
          // In case of new task and if field is empty, set default email address
        $taskInfo['postfix_quotaLimitDefault'] = '100';
      } 
      elseif( $parentObject->CMD == 'edit' ) 
      {
          // In case of edit, and editing a test task, set to internal value if not data was submitted already
        $taskInfo['postfix_quotaLimitDefault'] = $task->postfix_quotaLimitDefault;
      }
      else
      {
          // Otherwise set an empty value, as it will not be used anyway
        $taskInfo['postfix_quotaLimitDefault'] = '';
      }
    }
      // IF : field is empty, initialize extra field value

      // Write the code for the field
    $fieldID    = 'postfix_quotaLimitDefault';
    $fieldValue = htmlspecialchars( $taskInfo['postfix_quotaLimitDefault'] );
    $fieldCode  = '<input type="text" name="tx_scheduler[postfix_quotaLimitDefault]" id="' . $fieldID . '" value="' . $fieldValue . '" size="5" maxlength="5"/>';
    $additionalFields = array( );
    $additionalFields[$fieldID] = array
    (
      'code'     => $fieldCode,
      'label'    => 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaLimitDefault',
      'cshKey'   => '_MOD_tools_txschedulerM1',
      'cshLabel' => $fieldID
    );
      // Write the code for the field

    return $additionalFields;
  }

  /**
    * getFieldQuotaLimitRemove( )  : This method is used to define new fields for adding or editing a task
    *                                           In this case, it adds an email field
    *
    * @param array $taskInfo Reference to the array containing the info used in the add/edit form
    * @param object $task When editing, reference to the current task object. Null when adding.
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return array    Array containing all the information pertaining to the additional fields
    *                    The array is multidimensional, keyed to the task class name and each field's id
    *                    For each field it provides an associative sub-array with the following:
    *                        ['code']        => The HTML code for the field
    *                        ['label']        => The label of the field (possibly localized)
    *                        ['cshKey']        => The CSH key for the field
    *                        ['cshLabel']    => The code of the CSH label
    * @version       1.1.0
    * @since         1.1.0
    */
  private function getFieldQuotaLimitRemove( array &$taskInfo, $task, $parentObject ) 
  {
      // IF : field is empty, initialize extra field value
    if( empty( $taskInfo['postfix_quotaLimitRemove'] ) ) 
    {
      if( $parentObject->CMD == 'add' ) 
      {
          // In case of new task and if field is empty, set default email address
        $taskInfo['postfix_quotaLimitRemove'] = '105';
      } 
      elseif( $parentObject->CMD == 'edit' ) 
      {
          // In case of edit, and editing a test task, set to internal value if not data was submitted already
        $taskInfo['postfix_quotaLimitRemove'] = $task->postfix_quotaLimitRemove;
      }
      else
      {
          // Otherwise set an empty value, as it will not be used anyway
        $taskInfo['postfix_quotaLimitRemove'] = '';
      }
    }
      // IF : field is empty, initialize extra field value

      // Write the code for the field
    $fieldID    = 'postfix_quotaLimitRemove';
    $fieldValue = htmlspecialchars( $taskInfo['postfix_quotaLimitRemove'] );
    $fieldCode  = '<input type="text" name="tx_scheduler[postfix_quotaLimitRemove]" id="' . $fieldID . '" value="' . $fieldValue . '" size="3"  maxlength="3"/>';
    $additionalFields = array( );
    $additionalFields[$fieldID] = array
    (
      'code'     => $fieldCode,
      'label'    => 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaLimitRemove',
      'cshKey'   => '_MOD_tools_txschedulerM1',
      'cshLabel' => $fieldID
    );
      // Write the code for the field

    return $additionalFields;
  }

  /**
    * getFieldQuotaLimitWarn( )  : This method is used to define new fields for adding or editing a task
    *                                           In this case, it adds an email field
    *
    * @param array $taskInfo Reference to the array containing the info used in the add/edit form
    * @param object $task When editing, reference to the current task object. Null when adding.
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return array    Array containing all the information pertaining to the additional fields
    *                    The array is multidimensional, keyed to the task class name and each field's id
    *                    For each field it provides an associative sub-array with the following:
    *                        ['code']        => The HTML code for the field
    *                        ['label']        => The label of the field (possibly localized)
    *                        ['cshKey']        => The CSH key for the field
    *                        ['cshLabel']    => The code of the CSH label
    * @version       1.1.0
    * @since         1.1.0
    */
  private function getFieldQuotaLimitWarn( array &$taskInfo, $task, $parentObject ) 
  {
      // IF : field is empty, initialize extra field value
    if( empty( $taskInfo['postfix_quotaLimitWarn'] ) ) 
    {
      if( $parentObject->CMD == 'add' ) 
      {
          // In case of new task and if field is empty, set default email address
        $taskInfo['postfix_quotaLimitWarn'] = '80';
      } 
      elseif( $parentObject->CMD == 'edit' ) 
      {
          // In case of edit, and editing a test task, set to internal value if not data was submitted already
        $taskInfo['postfix_quotaLimitWarn'] = $task->postfix_quotaLimitWarn;
      }
      else
      {
          // Otherwise set an empty value, as it will not be used anyway
        $taskInfo['postfix_quotaLimitWarn'] = '';
      }
    }
      // IF : field is empty, initialize extra field value

      // Write the code for the field
    $fieldID    = 'postfix_quotaLimitWarn';
    $fieldValue = htmlspecialchars( $taskInfo['postfix_quotaLimitWarn'] );
    $fieldCode  = '<input type="text" name="tx_scheduler[postfix_quotaLimitWarn]" id="' . $fieldID . '" value="' . $fieldValue . '" size="3"  maxlength="3"/>';
    $additionalFields = array( );
    $additionalFields[$fieldID] = array
    (
      'code'     => $fieldCode,
      'label'    => 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaLimitWarn',
      'cshKey'   => '_MOD_tools_txschedulerM1',
      'cshLabel' => $fieldID
    );
      // Write the code for the field

    return $additionalFields;
  }


  /**
    * getFieldQuotaMode( )  : This method is used to define new fields for adding or editing a task
    *                                           In this case, it adds an email field
    *
    * @param array $taskInfo Reference to the array containing the info used in the add/edit form
    * @param object $task When editing, reference to the current task object. Null when adding.
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return array    Array containing all the information pertaining to the additional fields
    *                    The array is multidimensional, keyed to the task class name and each field's id
    *                    For each field it provides an associative sub-array with the following:
    *                        ['code']        => The HTML code for the field
    *                        ['label']        => The label of the field (possibly localized)
    *                        ['cshKey']        => The CSH key for the field
    *                        ['cshLabel']    => The code of the CSH label
    * @version       1.1.0
    * @since         1.1.0
    */
  private function getFieldQuotaMode( array &$taskInfo, $task, $parentObject ) 
  {
      // IF : field is empty, initialize extra field value
    if( empty( $taskInfo['postfix_quotaMode'] ) ) 
    {
      if( $parentObject->CMD == 'add' ) 
      {
          // In case of new task and if field is empty, set default email address
        $taskInfo['postfix_quotaMode'] = 'test';
      } 
      elseif( $parentObject->CMD == 'edit' ) 
      {
          // In case of edit, and editing a test task, set to internal value if not data was submitted already
        $taskInfo['postfix_quotaMode'] = $task->postfix_quotaMode;
      }
      else
      {
          // Otherwise set an empty value, as it will not be used anyway
        $taskInfo['postfix_quotaMode'] = '';
      }
    }
      // IF : field is empty, initialize extra field value

      // Write the code for the field
    $fieldID      = 'postfix_quotaMode';
    $fieldValue   = $taskInfo['postfix_quotaMode'];
    $labelRemove  = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaMode.remove' );
    $labelWarn    = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaMode.warn' );
    $labelTest    = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaMode.test' );
    $selected               = array( );
    $selected['remove']     = null;
    $selected['test']       = null;
    $selected['warn']       = null;
    $selected[$fieldValue]  = ' selected="selected"';
    
    $fieldCode    = '
                      <select name="tx_scheduler[postfix_quotaMode]" id="' . $fieldID . '" size="1" style="width:300px;">
                        <option value="remove"' . $selected['remove'] . '>' . $labelRemove . '</option>
                        <option value="warn"' . $selected['warn'] . '>' . $labelWarn . '</option>
                        <option value="test"' . $selected['test'] . '>' . $labelTest . '</option>
                      </select>
                    ';    
    $additionalFields = array( );
    $additionalFields[$fieldID] = array
    (
      'code'     => $fieldCode,
      'label'    => 'LLL:EXT:postfix/lib/scheduler/locallang.xml:label.quotaMode',
      'cshKey'   => '_MOD_tools_txschedulerM1',
      'cshLabel' => $fieldID
    );
      // Write the code for the field

    return $additionalFields;
  }

  
  /**
    * validateAdditionalFields( ) : This method checks any additional data that is relevant to the specific task
    *                               If the task class is not relevant, the method is expected to return TRUE
    *
    * @param array     $submittedData Reference to the array containing the data submitted by the user
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
    * @version       1.1.0
    * @since         1.1.0
    */
  public function validateAdditionalFields( array &$submittedData, tx_scheduler_Module $parentObject ) 
  {
    $bool_isValidatingSuccessful = true;
     
    if( ! $this->validateOS( $parentObject ) ) 
    {
      return false;
    } 

    if( ! $this->validateFieldPathToFolderWiDrafts( $submittedData, $parentObject ) ) 
    {
      $bool_isValidatingSuccessful = false;
    } 

    if( ! $this->validateFieldPostfixAdminEmail( $submittedData, $parentObject ) ) 
    {
      $bool_isValidatingSuccessful = false;
    } 

    if( ! $this->validateFieldQuotaMode( $submittedData, $parentObject ) ) 
    {
      $bool_isValidatingSuccessful = false;
    } 

    if( ! $this->validateFieldQuotaLimitDefault( $submittedData, $parentObject ) ) 
    {
      $bool_isValidatingSuccessful = false;
    } 

    if( ! $this->validateFieldQuotaLimitRemove( $submittedData, $parentObject ) ) 
    {
      $bool_isValidatingSuccessful = false;
    } 

    if( ! $this->validateFieldQuotaLimitWarn( $submittedData, $parentObject ) ) 
    {
      $bool_isValidatingSuccessful = false;
    } 


    if( ! $this->validateFieldQuotaReduceMailbox( $submittedData, $parentObject ) ) 
    {
      $bool_isValidatingSuccessful = false;
    } 

    return $bool_isValidatingSuccessful;
  }

  /**
    * validateFieldPathToFolderWiDrafts( )  : This method checks any additional data that is relevant to the specific task
    *                                     If the task class is not relevant, the method is expected to return TRUE
    *
    * @param array     $submittedData Reference to the array containing the data submitted by the user
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
    * @version       1.1.0
    * @since         1.1.0
    */
  private function validateFieldPathToFolderWiDrafts( array &$submittedData, tx_scheduler_Module $parentObject ) 
  {
    $bool_isValidatingSuccessful = true;

    $submittedData['postfix_pathToFolderWiDrafts'] = trim( $submittedData['postfix_pathToFolderWiDrafts'] );

    if( empty( $submittedData['postfix_pathToFolderWiDrafts'] ) ) 
    {
      $prompt = $this->msgPrefix . ': ' . $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:msg.enterPathToFolderWiDrafts' );
      $parentObject->addMessage( $prompt, t3lib_FlashMessage::ERROR );
      $bool_isValidatingSuccessful = false;
    } 

    return $bool_isValidatingSuccessful;
  }

  /**
    * validateFieldPostfixAdminEmail( )  : This method checks any additional data that is relevant to the specific task
    *                                     If the task class is not relevant, the method is expected to return TRUE
    *
    * @param array     $submittedData Reference to the array containing the data submitted by the user
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
    * @version       1.1.0
    * @since         1.1.0
    */
  private function validateFieldPostfixAdminEmail( array &$submittedData, tx_scheduler_Module $parentObject ) 
  {
    $bool_isValidatingSuccessful = true;

    $submittedData['postfix_postfixAdminEmail'] = trim( $submittedData['postfix_postfixAdminEmail'] );

    if( empty( $submittedData['postfix_postfixAdminEmail'] ) ) 
    {
      $prompt = $this->msgPrefix . ': ' . $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:msg.enterEmail' );
      $parentObject->addMessage( $prompt, t3lib_FlashMessage::ERROR );
      $bool_isValidatingSuccessful = false;
    } 

    return $bool_isValidatingSuccessful;
  }

  /**
    * validateFieldQuotaLimitDefault( )  : This method checks any additional data that is relevant to the specific task
    *                                     If the task class is not relevant, the method is expected to return TRUE
    *
    * @param array     $submittedData Reference to the array containing the data submitted by the user
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
    * @version       1.1.0
    * @since         1.1.0
    */
  private function validateFieldQuotaLimitDefault( array &$submittedData, tx_scheduler_Module $parentObject ) 
  {
    $bool_isValidatingSuccessful = true;

    $submittedData['postfix_quotaLimitDefault'] = ( int ) $submittedData['postfix_quotaLimitDefault'];

    switch( true )
    {
      case( $submittedData['postfix_quotaLimitDefault'] < 50 ):
        $prompt = $this->msgPrefix . ': ' . $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:msg.enterQuotaLimitDefault' );
        $parentObject->addMessage( $prompt, t3lib_FlashMessage::ERROR );
        $bool_isValidatingSuccessful = false;
        break;
      default:
        $bool_isValidatingSuccessful = true;
        break;
    }

    return $bool_isValidatingSuccessful;
  }

  /**
    * validateFieldQuotaLimitRemove( )  : This method checks any additional data that is relevant to the specific task
    *                                     If the task class is not relevant, the method is expected to return TRUE
    *
    * @param array     $submittedData Reference to the array containing the data submitted by the user
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
    * @version       1.1.0
    * @since         1.1.0
    */
  private function validateFieldQuotaLimitRemove( array &$submittedData, tx_scheduler_Module $parentObject ) 
  {
    $bool_isValidatingSuccessful = true;

    $submittedData['postfix_quotaLimitRemove'] = ( int ) $submittedData['postfix_quotaLimitRemove'];

    switch( true )
    {
      case( $submittedData['postfix_quotaLimitRemove'] < 100 ):
      case( $submittedData['postfix_quotaLimitRemove'] > 150 ):
        $prompt = $this->msgPrefix . ': ' . $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:msg.enterQuotaLimitRemove' );
        $parentObject->addMessage( $prompt, t3lib_FlashMessage::ERROR );
        $bool_isValidatingSuccessful = false;
        break;
      case( $submittedData['postfix_quotaLimitRemove'] <= $submittedData['postfix_quotaLimitWarn'] ):
        $prompt = $this->msgPrefix . ': ' . $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:msg.enterQuotaLimitRemoveMustBeBigger' );
        $parentObject->addMessage( $prompt, t3lib_FlashMessage::ERROR );
        $bool_isValidatingSuccessful = false;
        break;
      default:
        $bool_isValidatingSuccessful = true;
        break;
    }

    return $bool_isValidatingSuccessful;
  }

  /**
    * validateFieldQuotaLimitWarn( )  : This method checks any additional data that is relevant to the specific task
    *                                     If the task class is not relevant, the method is expected to return TRUE
    *
    * @param array     $submittedData Reference to the array containing the data submitted by the user
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
    * @version       1.1.0
    * @since         1.1.0
    */
  private function validateFieldQuotaLimitWarn( array &$submittedData, tx_scheduler_Module $parentObject ) 
  {
    $bool_isValidatingSuccessful = true;

    $submittedData['postfix_quotaLimitWarn'] = ( int ) $submittedData['postfix_quotaLimitWarn'];

    switch( true )
    {
      case( $submittedData['postfix_quotaLimitWarn'] < 50 ):
      case( $submittedData['postfix_quotaLimitWarn'] > 120 ):
        $prompt = $this->msgPrefix . ': ' . $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:msg.enterQuotaLimitWarn' );
        $parentObject->addMessage( $prompt, t3lib_FlashMessage::ERROR );
        $bool_isValidatingSuccessful = false;
        break;
      default:
        $bool_isValidatingSuccessful = true;
        break;
    }

    return $bool_isValidatingSuccessful;
  }

  /**
    * validateFieldQuotaMode( )  : This method checks any additional data that is relevant to the specific task
    *                                     If the task class is not relevant, the method is expected to return TRUE
    *
    * @param array     $submittedData Reference to the array containing the data submitted by the user
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
    * @version       1.1.0
    * @since         1.1.0
    */
  private function validateFieldQuotaMode( array &$submittedData, tx_scheduler_Module $parentObject ) 
  {
    $bool_isValidatingSuccessful = true;

      // Messages depending on mode
    switch( $submittedData['postfix_quotaMode'] )
    {
      case( 'remove' ):
        $prompt = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:msg.quotaMode.remove' );;
        $parentObject->addMessage( $prompt, t3lib_FlashMessage::WARNING );
        break;
      case( 'test' ):
        $prompt = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:msg.quotaMode.test' );;
        $parentObject->addMessage( $prompt, t3lib_FlashMessage::INFO );
        break;
      case( 'warn' ):
        $prompt = $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:msg.quotaMode.warn' );;
        $parentObject->addMessage( $prompt, t3lib_FlashMessage::INFO );
        break;
      default:
        $bool_isValidatingSuccessful = false;
        $prompt = $this->msgPrefix . ': ' . $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:msg.quotaMode.undefined' );;
        $parentObject->addMessage( $prompt, t3lib_FlashMessage::ERROR );
        break;
    }
      // Messages depending on mode

    return $bool_isValidatingSuccessful;
  }

  /**
    * validateFieldQuotaReduceMailbox( )  : This method checks any additional data that is relevant to the specific task
    *                                     If the task class is not relevant, the method is expected to return TRUE
    *
    * @param array     $submittedData Reference to the array containing the data submitted by the user
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
    * @version       1.1.0
    * @since         1.1.0
    */
  private function validateFieldQuotaReduceMailbox( array &$submittedData, tx_scheduler_Module $parentObject ) 
  {
    $bool_isValidatingSuccessful = true;

    $submittedData['postfix_quotaReduceMailbox'] = ( int ) $submittedData['postfix_quotaReduceMailbox'];

    switch( true )
    {
      case( $submittedData['postfix_quotaReduceMailbox'] < 50 ):
      case( $submittedData['postfix_quotaReduceMailbox'] > 100 ):
        $prompt = $this->msgPrefix . ': ' . $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:msg.enterQuotaReduceMailbox' );
        $parentObject->addMessage( $prompt, t3lib_FlashMessage::ERROR );
        $bool_isValidatingSuccessful = false;
        break;
      case( $submittedData['postfix_quotaReduceMailbox'] >= $submittedData['postfix_quotaLimitRemove'] ):
        $prompt = $this->msgPrefix . ': ' . $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:msg.enterQuotaReduceMailboxMustBeBigger' );
        $parentObject->addMessage( $prompt, t3lib_FlashMessage::ERROR );
        $bool_isValidatingSuccessful = false;
        break;
      default:
        $bool_isValidatingSuccessful = true;
        break;
    }

    return $bool_isValidatingSuccessful;
  }
  
  /**
    * validateOS( ) : This method checks any additional data that is relevant to the specific task
    *                               If the task class is not relevant, the method is expected to return TRUE
    *
    * @param array     $submittedData Reference to the array containing the data submitted by the user
    * @param tx_scheduler_Module $parentObject Reference to the calling object (Scheduler's BE module)
    * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
    * @version       1.1.0
    * @since         1.1.0
    */
  public function validateOS( tx_scheduler_Module $parentObject ) 
  {
    $bool_isValidatingSuccessful = true;
    
      // SWITCH : OS of the server
    switch( strtolower( PHP_OS ) )
    {
      case( 'linux' ):
          // Linux is proper: Follow the workflow
        break;
      default:
        $bool_isValidatingSuccessful = false;
        $prompt = $this->msgPrefix . ': ' . $GLOBALS['LANG']->sL( 'LLL:EXT:postfix/lib/scheduler/locallang.xml:msg.osIsNotSupported' );
        $prompt = str_replace( '###PHP_OS###', PHP_OS, $prompt );
        $parentObject->addMessage( $prompt, t3lib_FlashMessage::ERROR );
    }
      // SWITCH : OS of the server
      
    return $bool_isValidatingSuccessful;
  }

  /**
    * saveAdditionalFields( ) : This method is used to save any additional input into the current task object
    *                           if the task class matches
    *
    * @param array $submittedData Array containing the data submitted by the user
    * @param tx_scheduler_Task $task Reference to the current task object
    * @return void
    * @version       1.1.0
    * @since         1.1.0
    */
  public function saveAdditionalFields( array $submittedData, tx_scheduler_Task $task )
  {
    $this->saveFieldPathToFolderWiDrafts( $submittedData, $task );
    $this->saveFieldPostfixAdminEmail( $submittedData, $task );
    $this->saveFieldQuotaMode( $submittedData, $task );
    $this->saveFieldQuotaLimitDefault( $submittedData, $task );
    $this->saveFieldQuotaLimitRemove( $submittedData, $task );
    $this->saveFieldQuotaLimitWarn( $submittedData, $task );
    $this->saveFieldQuotaReduceMailbox( $submittedData, $task );
  }

  /**
    * saveFieldPathToFolderWiDrafts( ) : This method is used to save any additional input into the current task object
    *                           if the task class matches
    *
    * @param array $submittedData Array containing the data submitted by the user
    * @param tx_scheduler_Task $task Reference to the current task object
    * @return void
    * @version       1.1.0
    * @since         1.1.0
    */
  private function saveFieldPathToFolderWiDrafts( array $submittedData, tx_scheduler_Task $task )
  {
    $postfix_pathToFolderWiDrafts       = rtrim( $submittedData['postfix_pathToFolderWiDrafts'], '/' ) . '/';
    $task->postfix_pathToFolderWiDrafts = $postfix_pathToFolderWiDrafts;
  }
  
  /**
    * saveFieldPostfixAdminEmail( ) : This method is used to save any additional input into the current task object
    *                           if the task class matches
    *
    * @param array $submittedData Array containing the data submitted by the user
    * @param tx_scheduler_Task $task Reference to the current task object
    * @return void
    * @version       1.1.0
    * @since         1.1.0
    */
  private function saveFieldPostfixAdminEmail( array $submittedData, tx_scheduler_Task $task )
  {
    $task->postfix_postfixAdminEmail = $submittedData['postfix_postfixAdminEmail'];
  }

  /**
    * saveFieldQuotaMode( ) : This method is used to save any additional input into the current task object
    *                           if the task class matches
    *
    * @param array $submittedData Array containing the data submitted by the user
    * @param tx_scheduler_Task $task Reference to the current task object
    * @return void
    * @version       1.1.0
    * @since         1.1.0
    */
  private function saveFieldQuotaMode( array $submittedData, tx_scheduler_Task $task )
  {
    $task->postfix_quotaMode = $submittedData['postfix_quotaMode'];
  }

  /**
    * saveFieldQuotaLimitDefault( ) : This method is used to save any additional input into the current task object
    *                           if the task class matches
    *
    * @param array $submittedData Array containing the data submitted by the user
    * @param tx_scheduler_Task $task Reference to the current task object
    * @return void
    * @version       1.1.0
    * @since         1.1.0
    */
  private function saveFieldQuotaLimitDefault( array $submittedData, tx_scheduler_Task $task )
  {
    $postfix_quotaLimitDefault       = ( int ) $submittedData['postfix_quotaLimitDefault'];
    $task->postfix_quotaLimitDefault = $postfix_quotaLimitDefault;
  }

  /**
    * saveFieldQuotaLimitRemove( ) : This method is used to save any additional input into the current task object
    *                           if the task class matches
    *
    * @param array $submittedData Array containing the data submitted by the user
    * @param tx_scheduler_Task $task Reference to the current task object
    * @return void
    * @version       1.1.0
    * @since         1.1.0
    */
  private function saveFieldQuotaLimitRemove( array $submittedData, tx_scheduler_Task $task )
  {
    $postfix_quotaLimitRemove       = ( int ) $submittedData['postfix_quotaLimitRemove'];
    $task->postfix_quotaLimitRemove = $postfix_quotaLimitRemove;
  }

  /**
    * saveFieldQuotaLimitWarn( ) : This method is used to save any additional input into the current task object
    *                           if the task class matches
    *
    * @param array $submittedData Array containing the data submitted by the user
    * @param tx_scheduler_Task $task Reference to the current task object
    * @return void
    * @version       1.1.0
    * @since         1.1.0
    */
  private function saveFieldQuotaLimitWarn( array $submittedData, tx_scheduler_Task $task )
  {
    $postfix_quotaLimitWarn       = ( int ) $submittedData['postfix_quotaLimitWarn'];
    $task->postfix_quotaLimitWarn = $postfix_quotaLimitWarn;
  }

  /**
    * saveFieldQuotaReduceMailbox( ) : This method is used to save any additional input into the current task object
    *                           if the task class matches
    *
    * @param array $submittedData Array containing the data submitted by the user
    * @param tx_scheduler_Task $task Reference to the current task object
    * @return void
    * @version       1.1.0
    * @since         1.1.0
    */
  private function saveFieldQuotaReduceMailbox( array $submittedData, tx_scheduler_Task $task )
  {
    $postfix_quotaReduceMailbox       = ( int ) $submittedData['postfix_quotaReduceMailbox'];
    $task->postfix_quotaReduceMailbox = $postfix_quotaReduceMailbox;
  }

  
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/postfix/lib/scheduler/class.tx_postfix_quotatask_additionalfieldprovider.php'])) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/postfix/lib/scheduler/class.tx_postfix_quotatask_additionalfieldprovider.php']);
}

?>