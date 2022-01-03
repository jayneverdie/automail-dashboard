<?php

namespace App\Media;

use App\Common\View;

class MediaController
{
	public function __construct()
	{
		$this->view = new View;
	}

	public function all($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'all',
			'access' => 'booking|shipping_document'
		]);
	}

	public function shipping($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'shipping_doc_all',
			'access' => 'shipping_doc_all'
		]);
	}

	public function TireGroup_airwaybill($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'TireGroup_airwaybill_document'
		]);
	}

	public function TireGroup_shipping($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'TireGroup_shipping_document'
		]);
	}

	public function TireGroup_vessel($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'TireGroup_vessel_document'
		]);
	}

	public function TireGroup_loading($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'TireGroup_loading_document'
		]);
	}

	public function TireGroup_booking_new($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'TireGroup_booking_new_document'
		]);
	}

	public function TireGroup_booking_revise($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'TireGroup_booking_revise_document'
		]);
	}

	public function booking($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'booking_confirmation'
		]);
	}

	public function commercial($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'commercial_invoice'
		]);
	}

	public function camso($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'camso',
			'access' => 'C-1441|C-2536|ISF'
		]);
	}

	public function tiregroup($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'tiregroup',
			'access' => 'booking|shipping_doc'
		]);
	}

	public function tireGroup_shippingdoc($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'tiregroup/shipping_doc',
			'access' => 'shipping_doc'
		]);
	}

	public function tireGroup_booking($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'tiregroup/booking',
			'access' => 'booking'
		]);
	}

	public function tiregroup_logs_shipping($request, $response, $args)
	{
		return  $this->view->render('pages/media-logs', [
			'path' => 'tiregroup',
			'access' => 'booking|shipping_doc'
		]);
	}

	public function aot($request, $response, $args)
	{
		return  $this->view->render('pages/media', [
			'path' => 'aot',
			'access' => 'tbc_si'
		]);
	}

	public function aot_logs($request, $response, $args)
	{
		return  $this->view->render('pages/media-logs', [
			'path' => 'aot',
			'access' => 'tbc_si|tbc_vgm|shipping_doc_insp'
		]);
	}
	public function api($request, $response, $args)
	{
		return  $this->view->render('pages/media', [
			'path' => 'api',
			'access' => '_'
		]);
	}

	public function camso_logs($request, $response, $args)
	{
		return $this->view->render('pages/media-logs', [
			'path' => 'camso',
			'access' => 'C-1441|C-2536|ISF'
		]);
	}

	public function cust_inv($request, $response, $args) {
		return $this->view->render('pages/media', [
			'path' => 'customs_invoice',
			'access' => 'by_truck'
		]);
	}
	
	public function shortship($request, $response, $args)
	{
		return $this->view->render('pages/media', [
			'path' => 'shortship',
			'access' => 'shortship'
		]);
	}

	public function api_shipping($request, $response, $args)
	{
		return  $this->view->render('pages/media', [
			'path' => 'api_shipping',
			'access' => 'insp_cds|insp_cif|doc'
		]);
	}

	public function api_shipping_logs($request, $response, $args)
	{
		return  $this->view->render('pages/media-logs', [
			'path' => 'api_shipping',
			'access' => 'insp_cds|insp_cif|doc'
		]);
	}

	public function shipping_logs($request, $response, $args)
	{
		return $this->view->render('pages/media-logs', [
			'path' => 'shipping_doc_all',
			'access' => 'shipping_doc_all'
		]);
	}
}
