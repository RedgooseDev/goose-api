<?php
namespace Core;
use Exception, Controller;

if (!defined('__API_GOOSE__')) exit();

/**
 * delete article
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
		'table' => 'articles',
		'srl' => $srl,
	]);

	// remove thumbnail image
  Controller\files\UtilForFiles::removeThumbnailImage($this, $srl);

	// remove files
  Controller\files\UtilForFiles::removeAttachFiles($this, $srl, 'articles');

	// remove item
	$output = Controller\Main::delete($this, (object)[
		'table' => 'articles',
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
