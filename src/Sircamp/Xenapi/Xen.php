<?php namespace Sircamp\Xenapi;

use Illuminate\Support\Collection;
use Respect\Validation\Validator;
use Sircamp\Xenapi\Connection\XenConnection;
use Sircamp\Xenapi\Connection\XenResponse;
use Sircamp\Xenapi\Element\XenHost;
use Sircamp\Xenapi\Element\XenVirtualMachine;

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

    public function getPool()
    {
        $response = new XenResponse($this->xenConnection->pool__get_all_records());


        if($response->getValue()) {
            return array_first($response->getValue());
        }

        return null;
	}

    public function getAllHosts()
    {
        $response = new XenResponse($this->xenConnection->host__get_all());


        if($response->getValue()) {
            return (new Collection($response->getValue()))->map(function ($item) {
                return new XenHost($this->xenConnection, null, $item);
            });
        }

        return null;
	}

    public function getAllVMs()
    {
        $response = new XenResponse($this->xenConnection->VM__get_all());

        if($response->getValue()) {
            return (new Collection($response->getValue()))->map(function ($item) {
                return (new XenVirtualMachine($this->xenConnection, null, $item));
            });
        }

        return null;
	}

    public function getAllVBDs()
    {
        $vbds = $this->xenConnection->VBD__get_all_records();
        $vdis = $this->xenConnection->VDI__get_all_records();

        if($vbds->getValue()) {
            return (new Collection($vbds->getValue()))->map(function ($item, $key) use ($vdis) {
                $object = (object)$item;
                $object->vdi = (new Collection($vdis->getValue()))->filter(function ($vdi) use ($key) {
                    if(count($vdi['VBDs']) > 0){
                        return in_array($key, $vdi['VBDs']);
                    }
                });

                return $object;
            });
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