<?php
namespace Core;
use Exception;

/**
 * Goose
 * 인스톨이 된 상태에서 진행되는 행동에 관한 클래스.
 * - 설정파일 불러오기
 * - 토큰 검사
 * - 라우터 초기화
 * - url 라우트에 의한 컨트롤러 실행
 *
 * @property Router router
 * @property string target
 * @property array params
 * @property Model model
 */

class Goose {

  public function __construct()
  {
    $this->router = new Router();
    $this->target = null;
    $this->params = null;
    $this->model = null;
  }

  /**
   * routing to controller
   * 라우팅에 의한 해석된 값으로 컨트롤러로 넘겨주는 과정
   */
  private function turningPoint()
  {
    try
    {
      // check $target
      if (!$this->target)
      {
        throw new Exception(Message::make('error.notFound', 'target'), 404);
      }

      // search controller
      if (!file_exists(__API_PATH__.'/controller/'.$this->target.'.php'))
      {
        throw new Exception(Message::make('error.notFound', 'controller'), 404);
      }

      require __API_PATH__.'/controller/'.$this->target.'.php';
    }
    catch(Exception $e)
    {
      Error::data($e->getMessage(), $e->getCode());
    }
  }

  /**
   * Play app trigger
   *
   * @throws Exception
   */
  public function run()
  {
    // initialize routing
    $this->router->init();

    // check router match
    if (!$this->router->match)
    {
      return Error::data(Message::make('msg.notFoundMatch'), 404);
    }

    // set router values
    $this->target = $this->router->match['target'];
    $this->params = $this->router->match['params'];

    // set model
    $this->model = new Model();

    // run turning point
    $this->turningPoint();
  }

}
