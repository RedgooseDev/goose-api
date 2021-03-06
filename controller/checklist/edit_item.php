<?php
namespace Core;
use Exception, Controller;
use Controller\checklist\UtilForChecklist;

if (!defined('__API_GOOSE__')) exit();

/**
 * edit checklist item
 *
 * @var Goose|Connect $this
 */

try
{
  // check and set srl
  $srl = (int)$this->params['srl'];
  if (!($srl && $srl > 0))
  {
    throw new Exception(Message::make('error.notFound', 'srl'));
  }

  // check post values
  Util::checkExistValue($this->post, [ 'content' ]);

  // set percent into content
  $percent = UtilForChecklist::getPercentIntoCheckboxes($this->post->content);

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem($this, (object)[
    'table' => 'checklist',
    'srl' => $srl,
  ]);

  // adjust content
  $content = UtilForChecklist::adjustContent($this->post->content);

  // set output
  $output = Controller\Main::edit($this, (object)[
    'table' => 'checklist',
    'srl' => $srl,
    'data' => [
      "content='{$content}'",
      "percent={$percent}",
    ],
  ]);

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output data
  return Output::data($output);
}
catch (Exception $e)
{
  if (isset($this->model)) $this->model->disconnect();
  return Error::data($e->getMessage(), $e->getCode());
}
