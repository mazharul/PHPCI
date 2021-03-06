<?php

namespace PHPCI\Controller;
use b8,
	b8\Registry,
	PHPCI\Model\User,
	b8\Form;

class UserController extends b8\Controller
{
	public function init()
	{
		$this->_userStore		= b8\Store\Factory::getStore('User');
	}

	public function index()
	{
		$users			= $this->_userStore->getWhere(array(), 1000, 0, array(), array('email' => 'ASC'));
		$view			= new b8\View('User');
		$view->users	= $users;

		return $view->render();
	}

	public function add()
	{
		if(!Registry::getInstance()->get('user')->getIsAdmin())
		{
			throw new \Exception('You do not have permission to do that.');
		}

		$method	= Registry::getInstance()->get('requestMethod');

		if($method == 'POST')
		{
			$values = $this->getParams();
		}
		else
		{
			$values = array();
		}

		$form	= $this->userForm($values);

		if($method != 'POST' || ($method == 'POST' && !$form->validate()))
		{
			$view			= new b8\View('UserForm');
			$view->type		= 'add';
			$view->user		= null;
			$view->form		= $form;

			return $view->render();
		}

		$values				= $form->getValues();
		$values['is_admin']	= $values['admin'] ? 1 : 0;
		$values['hash']		= password_hash($values['password'], PASSWORD_DEFAULT);

		$user = new User();
		$user->setValues($values);

		$user = $this->_userStore->save($user);

		header('Location: /user');
		die;
	}

	public function edit($id)
	{
		if(!Registry::getInstance()->get('user')->getIsAdmin())
		{
			throw new \Exception('You do not have permission to do that.');
		}

		$method		= Registry::getInstance()->get('requestMethod');
		$user	= $this->_userStore->getById($id);

		if($method == 'POST')
		{
			$values = $this->getParams();
		}
		else
		{
			$values				= $user->getDataArray();
			$values['admin']	= $values['is_admin'];
		}

		$form	= $this->userForm($values, 'edit/' . $id);

		if($method != 'POST' || ($method == 'POST' && !$form->validate()))
		{
			$view			= new b8\View('UserForm');
			$view->type		= 'edit';
			$view->user		= $user;
			$view->form		= $form;

			return $view->render();
		}

		$values				= $form->getValues();
		$values['is_admin']	= $values['admin'] ? 1 : 0;

		if(!empty($values['password']))
		{
			$values['hash']		= password_hash($values['password'], PASSWORD_DEFAULT);
		}

		$user->setValues($values);
		$user = $this->_userStore->save($user);

		header('Location: /user');
		die;
	}

	protected function userForm($values, $type = 'add')
	{
		$form = new Form();
		$form->setMethod('POST');
		$form->setAction('/user/' . $type);
		$form->addField(new Form\Element\Csrf('csrf'));

		$field = new Form\Element\Email('email');
		$field->setRequired(true);
		$field->setLabel('Email Address');
		$field->setClass('span4');
		$form->addField($field);

		$field = new Form\Element\Text('name');
		$field->setRequired(true);
		$field->setLabel('Name');
		$field->setClass('span4');
		$form->addField($field);

		$field = new Form\Element\Password('password');
		$field->setRequired(true);
		$field->setLabel('Password' . ($type == 'edit' ? ' (leave blank to keep current password)' : ''));
		$field->setClass('span4');
		$form->addField($field);

		$field = new Form\Element\Checkbox('admin');
		$field->setRequired(false);
		$field->setCheckedValue(1);
		$field->setLabel('Is this user an administrator?');
		$form->addField($field);

		$field = new Form\Element\Submit();
		$field->setValue('Save User');
		$field->setClass('btn-success');
		$form->addField($field);

		$form->setValues($values);
		return $form;
	}

	public function delete($id)
	{
		if(!Registry::getInstance()->get('user')->getIsAdmin())
		{
			throw new \Exception('You do not have permission to do that.');
		}
		
		$user	= $this->_userStore->getById($id);
		$this->_userStore->delete($user);

		header('Location: /user');
	}
}