<?php if (!defined('BASEPATH')) exit ('No direct script access allowed');

class FlightTarco extends MX_Controller {

    const MODULE = "FlightTarco";
    const SLUG = "trflight";
    const VIEW = "modules/tarco_flight";

    private $config = [];

    public function __construct() 
	{
		parent :: __construct();

		$chk = $this->App->service('ModuleService')->isActive(self::Module);
        if (!$chk) { Error_404($this); }

        modules::load('Front');
        $this->data['lang_set'] = $this->session->userdata('set_lang');
        $this->data['phone'] = $this->load->get_var('phone');
        $this->data['contactemail'] = $this->load->get_var('contactemail');
        $defaultlang = pt_get_default_language();
        if (empty($this->data['lang_set'])) {
          $this->data['lang_set'] = $defaultlang;
        }
        $this->lang->load("front", $this->data['lang_set']);
        $this->data['appModule'] = self::Module;

        $this->config = $this->App->service('ModuleService')->get(self::Module)->apiConfig;
    }

    public function index()
    {
        redirect('/');
    }

    /**
     * @param mixed ...$args
     */
    public function search(...$args)
    {
        echo 'listing page';

//        $this->theme->view(self::VIEW.'/listing', $data, $this);
    }

    public function checkout()
    {
        echo 'checkout page';
    }

    public function booking()
    {
        echo 'booking page';
    }
}
