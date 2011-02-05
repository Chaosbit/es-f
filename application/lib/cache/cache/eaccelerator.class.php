<?php
/**
 * Cache class using EAccelerator opcode cache
 *
 * The following settings are supported:
 * - @c token : used to build unique cache ids (optional)
 *
 * @ingroup    Cache
 * @author     Knut Kohl <knutkohl@users.sourceforge.net>
 * @copyright  2007-2011 Knut Kohl
 * @license    GNU General Public License http://www.gnu.org/licenses/gpl.txt
 * @version    1.0.0
 * @version    $Id$
 */
class Cache_EAccelerator extends Cache {

  // -------------------------------------------------------------------------
  // PUBLIC
  // -------------------------------------------------------------------------

  /**
   * @name Implemented abstract functions
   * @{
   */
  public static function available() {
    return (extension_loaded('eaccelerator') AND function_exists('eaccelerator_put'));
  }

  public function set( $id, $data, $ttl=0 ) {
    // optimized for probability Set -> Delete -> Clear
    if ($data !== NULL) {
      $this->delete($id);
      $id = $this->id($id);
      // calculate time to live
      return (eaccelerator_lock($id) AND
              eaccelerator_put($id, $this->serialize(array($this->ts, $ttl, $data))) AND
              eaccelerator_unlock($id));
    } elseif ($id !== NULL) { // AND $data === NULL
      return $this->delete($id);
    } else { // $id === NULL AND $data === NULL
      return $this->clear();
    }
  }

  public function get( $id, $expire=0 ) {
    $id = $this->id($id);
    if (!eaccelerator_lock($id) OR
        !$cached = eaccelerator_get($id) OR
        !eaccelerator_unlock($id) OR
        !$cached = $this->unserialize($cached)) return;

    // split into store time, ttl, data
    list($ts, $ttl, $data) = $cached;
    // Data valid?
    if ($this->valid($ts, $ttl, $expire)) return $data;
    // else drop data for this key
    $this->delete($id);
  }

  public function delete( $id ) {
		$id = $this->id($id);
		return (eaccelerator_lock($id) AND
            eaccelerator_rm($id) AND
            eaccelerator_unlock($id));
  }

  public function flush() {
		return (eaccelerator_clean() AND eaccelerator_clear());
  }
  /** @} */

  // -------------------------------------------------------------------------
  // PROTECTED
  // -------------------------------------------------------------------------

  protected function __construct( $settings=array() ) {
    if (!self::available())
      throw new CacheException(__CLASS__.': Extension EAccelerator not loaded.', 9);
    parent::__construct($settings);
    eaccelerator_caching(TRUE);
  }

}