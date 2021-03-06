<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * delete nest
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

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem($this, (object)[
    'table' => 'nests',
    'srl' => $srl,
  ]);

  // remove articles
  $articles = $this->model->getItems((object)[
    'table' => 'articles',
    'field' => 'srl',
    'where' => 'nest_srl='.$srl,
  ]);
  if ($articles->data && count($articles->data))
  {
    foreach($articles->data as $k=>$v)
    {
      // remove thumbnail image
      Controller\files\UtilForFiles::removeThumbnailImage($this, $v->srl);
      // remove files
      Controller\files\UtilForFiles::removeAttachFiles($this, $v->srl, 'articles');
    }
    // remove articles
    $this->model->delete((object)[
      'table' => 'articles',
      'where' => 'nest_srl='.$srl,
    ]);
  }

  // remove categories
  $categoriesCount = $this->model->getCount((object)[
    'table' => 'categories',
    'where' => 'nest_srl='.$srl,
  ]);
  if ($categoriesCount->data > 0)
  {
    $this->model->delete((object)[
      'table' => 'categories',
      'where' => 'nest_srl='.$srl,
    ]);
  }

  // remove nest
  $output = Controller\Main::delete($this, (object)[
    'table' => 'nests',
    'srl' => $srl,
  ]);

  // set output
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
