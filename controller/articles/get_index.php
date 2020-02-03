<?php
namespace Core;
use Exception, Controller;

if (!defined('__GOOSE__')) exit();

/**
 * get articles
 *
 * @var Goose $this
 */

try
{
  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessIndex($this->model, true);

  // set where
  $where = '';
  if ($app = $_GET['app'])
  {
    $where .= ' and app_srl='.$app;
  }
  if ($nest = $_GET['nest'])
  {
    $where .= ' and nest_srl='.$nest;
  }
  if ($category = $_GET['category'])
  {
    $where .= ($category === 'null') ? ' and category_srl IS NULL' : ' and category_srl='.$category;
  }
  if ($q = $_GET['q'])
  {
    $where .= ' and (title LIKE \'%'.$q.'%\' or content LIKE \'%'.$q.'%\')';
  }
  // 모든 글 가져오기
  if ($_GET['visible_type'] === 'all')
  {
    // 관리자가 아닐경우 `type=NULL or type!=NULL and user_srl` 조건이 부합되는 쿼리
    if (!$token->data->admin)
    {
      $user_srl = isset($token->data->user_srl) ? (int)$token->data->user_srl : '';
      $checkUserSrl = $user_srl ? ' and user_srl=\''.$user_srl.'\'' : '';
      $where .= ' and ((NOT type IS NULL and user_srl=\''.$user_srl.'\') or (type IS NULL'.$checkUserSrl.'))';
    }
  }
  // 공개된 글 가져오기
  else
  {
    // 관리자가 아니고 유저토큰인 경우라면 자신의 데이터만 가져오기
    if (!$token->data->admin && isset($token->data->user_srl))
    {
      $where .= ' and user_srl='.(int)$token->data->user_srl;
    }
    // type 필드가 `NULL`일때 공개된 글입니다.
    $where .= ' and type IS NULL';
  }

  // set output
  $output = Controller\Main::index((object)[
    'model' => $this->model,
    'table' => 'articles',
    'where' => $where,
    'json_field' => ['json']
  ]);

  // get category name
  if ($output->data && Util::checkKeyInExtField('category_name'))
  {
    $output->data->index = Controller\articles\UtilForArticles::extendCategoryNameInItems(
      $this->model,
      $output->data->index
    );
  }

  // get nest name
  if ($output->data && Util::checkKeyInExtField('nest_name'))
  {
    $output->data->index = Controller\articles\UtilForArticles::extendNestNameInItems(
      $this->model,
      $output->data->index
    );
  }

  // get next page
  if ($output->data && Util::checkKeyInExtField('next_page'))
  {
    $nextPage = Controller\articles\UtilForArticles::getNextPage(
      $this->model,
      $where
    );
    if ($nextPage) $output->data->nextPage = $nextPage;
  }

  // set token
  if ($token) $output->_token = $token->jwt;

  // disconnect db
  $this->model->disconnect();

  // output
  Output::data($output);
}
catch (Exception $e)
{
  $this->model->disconnect();
  Error::data($e->getMessage(), $e->getCode());
}
