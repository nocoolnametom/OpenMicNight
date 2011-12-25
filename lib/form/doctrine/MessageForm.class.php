<?php

/**
 * Message form.
 *
 * @package    OpenMicNight
 * @subpackage form
 * @author     Tom Doggett
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class MessageForm extends BaseMessageForm
{
  public function configure()
  {
      $this->widgetSchema['previous_message_id'] = new sfWidgetFormInputHidden();
      $this->widgetSchema['recipient_id'] = new sfWidgetFormInputHidden();
      unset($this['sender_id']);
  }
}
