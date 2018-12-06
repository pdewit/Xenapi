<?php namespace Sircamp\Xenapi;

use Respect\Validation\Validator as Validator;
use Sircamp\Xenapi\Connection\XenConnection as XenConnection;
use Sircamp\Xenapi\Connection\XenResponse as XenResponse;
use Sircamp\Xenapi\Element\XenHost as XenHost;
use Sircamp\Xenapi\Element\XenVirtualMachine as XenVirtualMachine;

class Xen
{

	private $xenConnection = null;

	public function __construct($url, $user, $password)
	{


		if (!Validator::ip()->validate($url))
		{

			throw new \InvalidArgumentException("'url' value mast be an ipv4 address", 1);

		}
		if (!Validator::stringType()->validate($user))
		{
			throw new \InvalidArgumentException("'user' value mast be an non empty string", 1);
		}

		if (!Validator::stringType()->validate($password))
		{
			throw new \InvalidArgumentException("'password' value mast be an non empty string", 1);
		}

		$this->xenConnection = new XenConnection();
		try
		{
			$this->xenConnection->_setServer($url, $user, $password);
		}
		catch (\Exception $e)
		{
			die($e->getMessage());
		}
	}

    public function getAllHosts()
    {
        $response = new XenResponse($this->xenConnection->host__get_all());

        $record = new XenResponse($this->xenConnection->host__get_metrics($response->getValue()[0]));

        if($response->getValue()){
            return $response->getValue()[0];
        }

        return null;
	}

	/**
	 * Get VM inside Hypervisor from name.
	 *
	 * @param mixed $name the name of VM
	 *
	 * @return mixed
	 */
	public function getVMByNameLabel($name)
	{
		$response = new XenResponse($this->xenConnection->VM__get_by_name_label($name));

		if($response->getValue()){
			return new XenVirtualMachine($this->xenConnection, $name, $response->getValue()[0]);
		}
		return null;

	}

	/**
	 * Get HOST from name.
	 *
	 * @param mixed $name the name of HOST
	 *
	 * @return mixed
	 */
	public function getHOSTByNameLabel($name)
	{
		$response = new XenResponse($this->xenConnection->host__get_by_name_label($name));
		if($response->getValue()){
			return new XenHost($this->xenConnection, $name, $response->getValue()[0]);
		}
		return null;
	}


}