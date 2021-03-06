<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * delete app
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
    'table' => 'apps',
    'srl' => $srl,
  ]);

  // get articles list
  $articles = $this->model->getItems((object)[
    'table' => 'articles',
    'field' => 'srl',
    'where' => 'app_srl='.$srl,
  ]);
  if ($articles->data && count($articles->data) > 0)
  {
    foreach($articles->data as $k=>$v)
    {
      // remove comments
      $this->model->delete((object)[
        'table' => 'comments',
        'where' => 'article_srl='.$v->srl,
      ]);
      // remove thumbnail image
      Controller\files\UtilForFiles::removeThumbnailImage($this, $v->srl);
      // remove files
      Controller\files\UtilForFiles::removeAttachFiles($this, $v->srl, 'articles');
    }
    // remove articles
    $this->model->delete((object)[
      'table' => 'articles',
      'where' => 'app_srl='.$srl,
    ]);
  }

  // get nests list
  $nests = $this->model->getItems((object)[
    'table' => 'nests',
    'field' => 'srl',
    'where' => 'app_srl='.$srl,
  ]);
  if ($nests->data && count($nests->data))
  {
    // remove categories
    foreach($nests->data as $k=>$v)
    {
      $this->model->delete((object)[
        'table' => 'categories',
        'where' => 'nest_srl='.(int)$v->srl,
      ]);
    }
    // remove nests
    $this->model->delete((object)[
      'table' => 'nests',
      'where' => 'app_srl='.$srl,
    ]);
  }

  // remove app
  $output = Controller\Main::delete($this, (object)[
    'table' => 'apps',
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
