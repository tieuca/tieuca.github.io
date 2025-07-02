<?php
namespace OXT\App;

use OXT\App\Settings;

class Base {
    use CommonProperties;
	protected $features = [];

	public function __construct() {
        $this->initialize_common_properties(); // ✅ Gọi từ trait
	    
		foreach ( $this->features as $feature ) {
			if ( Settings::is_feature_active( $feature ) ) {
				$this->$feature();
			}
		}
	}
}
