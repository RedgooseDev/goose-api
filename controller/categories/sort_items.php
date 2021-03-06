<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * sort turn category
 *
 * @var Goose|Connect $this
 */

try
{
  // check post values
  Util::checkExistValue($this->post, [ 'nest_srl', 'srls' ]);

  // connect db
  $this->model->connect();

  // check access
  $token = Controller\Main::checkAccessItem($this, (object)[
    'table' => 'nests',
    'srl' => (int)$this->post->nest_srl,
  ]);

  // set srls
  $srls = explode(',', $this->post->srls);

  // update db
  foreach ($srls as $k=>$v)
  {
    $this->model->edit((object)[
      'table' => 'categories',
      'where' => 'nest_srl='.(int)$this->post->nest_srl.' and srl='.$v,
      'data' => [ 'turn='.($k+1) ],
    ]);
  }

  // set output
  $output = (object)[];
  $output->code = 200;

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
