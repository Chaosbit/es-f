<?php
/** @defgroup Module Modules

*/

/**
 * Abstract Module class
 *
 * @ingroup    Module
 * @author     Knut Kohl <knutkohl@users.sourceforge.net>
 * @copyright  2007-2011 Knut Kohl
 * @license    GNU General Public License http://www.gnu.org/licenses/gpl.txt
 * @version    1.0.0
 * @version    $Id: v2.4.1-62-gb38404e 2011-01-30 22:35:34 +0100 $
 */
abstract class esf_Module extends esf_Extension {

  /**
   * Handle called action routines
   *
   * Simple actions
   * - IndexAction
   * - ShowAction
   *
   * Actions during processing
   * - IndexHeaderAction
   * - IndexContentAction
   * - IndexFooterAction
   *
   * @param string $action
   * @param string $step Header|Content|Footer
   * @return void
   */
  public function handle( $action, $step='' ) {
    $this->Action = $action;
    do {
      $saveaction = $this->Action;
      // Check for supported action
      if (in_array($this->Action, $this->handles())) {
        $method = $this->Action . $step . 'Action';
        // Check for method
        if (method_exists($this, $method)) {
          // >> Debug
          Yryie::Info(get_class($this).'::'.$method.'()');
          // << Debug
          $this->$method();
        }
      } else {
        if (DEVELOP)
          Messages::Error('Not handled action "'.$this->Action.'" in '.get_class($this));
        // redirect to 1st handled action (mostly "index") of module
        $this->redirect($this->ExtensionName, reset($this->handles()));
      }
    } while ($saveaction != $this->Action);
  }

  //--------------------------------------------------------------------------
  // PROTECTED
  //--------------------------------------------------------------------------

  /**
   * Actual module action
   *
   * @var string $Action
   */
  protected $Action;

  /**
   * Forward to another action
   *
   * @param $action string
   */
  protected function forward( $action='index' ) {
    $this->Action = $action;
    Registry::set('esf.Action', $this->Action);
  }

  /**
   * Redirect to another module/action
   *
   * @param $module string
   * @param $action string
   * @param $params array Additional parameters
   * @param $anchor string
   */
  protected function redirect( $module=NULL, $action=NULL, $params=array(), $anchor=NULL ) {
    Core::redirect(Core::URL(array('module'=>$module, 'action'=>$action,
                                   'params'=>$params, 'anchor'=>$anchor)));
  }

}