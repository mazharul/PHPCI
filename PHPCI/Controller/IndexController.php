<?php

namespace PHPCI\Controller;
use b8;

class IndexController extends b8\Controller
{
	public function init()
	{
		$this->_buildStore		= b8\Store\Factory::getStore('Build');
		$this->_projectStore	= b8\Store\Factory::getStore('Project');
	}

	public function index()
	{
		$projects		= $this->_projectStore->getWhere(array(), 50, 0, array(), array('title' => 'ASC'));
		$view			= new b8\View('Index');
		$view->builds	= $this->getLatestBuildsHtml();
		$view->projects	= $projects['items'];

		return $view->render();
	}

	public function latest()
	{
		die($this->getLatestBuildsHtml());
	}

	protected function getLatestBuildsHtml()
	{
		$builds			= $this->_buildStore->getWhere(array(), 10, 0, array(), array('id' => 'DESC'));
		$view			= new b8\View('BuildsTable');
		$view->builds	= $builds['items'];

		return $view->render();
	}
}