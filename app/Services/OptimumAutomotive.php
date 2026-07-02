<?php

namespace App\Services;

use ReflectionClass;
use ReflectionProperty;
use SoapClient;

/**
 * This class will be extended
 * by all Optimum Automotive option class
 * which will need authentication
 */
abstract class OptimumAutomotiveOption {

    public function __construct($args = [])
    {
        $this->initPropertiesValues($args);
    }

    /**
     * Init properties values
     */
    private function initPropertiesValues($args) {
        foreach ($args as $name => $value) {
            $this->__set($name, $value);
        }
    }

    public function __get(string $name) {
        if (!property_exists($this, $name)) {
            throw new \Exception("Invalid property '{$name}'");
        }

        return $this->$name;
    }

    public function __set(string $name, $value) {
        if (!property_exists($this, $name)) {
            throw new \Exception("Invalid argument '{$name}' for class '{get_class($this)}'");
        }
        
        $currentClass = get_class($this);
        
        $reflectionClass = new ReflectionClass($currentClass);
        $reflectionProperty = new ReflectionProperty($currentClass, $name);
        $type = $reflectionProperty->getType();

        if (!is_null($type)) {
            $class = $type->getName();

            // OptimumAutomotiveOption type
            if (is_subclass_of($class, __NAMESPACE__ . '\\OptimumAutomotiveOption')) {
                if (!is_array($value)) {
                    throw new \Exception("Argument '{$name}' value must be an array");
                }
                
                $reflectionClass->getProperty($name)->setValue($this, new $class($value));
                return;
            }
        }

        $reflectionClass->getProperty($name)->setValue($this, $value);
    }
}

/**
 * All requests to the Optimum Automotive WSDL
 * will need an authentication
 */
class Authentification extends OptimumAutomotiveOption {

    /**
     * User name
     */
    public $user;

    /**
     * User password
     */
    public $password;
}

/**
 * This class will be extended
 * by all Optimum Automotive option class
 * which will need authentication
 */
abstract class OptimumAutomotiveOptionWithAuth extends OptimumAutomotiveOption {

    /**
     * Authentication param
     */
    public Authentification $authentification;

    public function __construct($args = [])
    {
        parent::__construct($args);
    }
}



/**
 * Optimum Automotive service
 */
class OptimumAutomotive {

    /**
     * User name
     */
    private string $user;

    /**
     * User password
     */
    private string $password;

    /**
     * WSDL URL
     */
    private string $wsdl;

    /**
     * SOAP client
     */
    private $client;

    public function __construct()
    {
        // Get WSDL URL from env file
        $this->wsdl = env('OPTIMUM_AUTOMOTIVE_WSDL', "https://ws.mappingcontrol.com/mapping_ws.asmx?wsdl");

        // Init soap client with WSDL URL
        $this->client = new SoapClient($this->wsdl, [
            'trace' => 1, 
            'exceptions'  => 1
        ]);

        $this->setAuth(
            env('OPTIMUM_AUTOMOTIVE_USER', ""), 
            env('OPTIMUM_AUTOMOTIVE_PASSWORD', "")
        );
    }

    /**
     * Custom method call
     */
    public function __call($method, $args)
    {
        // Check if $method exists as a class
        // and it is a sub class of OptimumAutomotiveOptionWithAuth

        $class = __NAMESPACE__ . '\\' . $method;
        
        if (!class_exists($class) || !is_subclass_of($class, __NAMESPACE__ . '\\OptimumAutomotiveOptionWithAuth')) {
            throw new \Exception("Call to undefined method '{$method}'");
        }

        if (!isset($this->user) || !isset($this->password)) {
            throw new \Exception("Please set user and password for authentication via setAuth(string \$user, string \$password) method");
        }

        // instatiate an OptimumAutomotiveOptionWithAuth object

        $params = new $class([
            'authentification' => [
                'user' => $this->user,
                'password' => $this->password
            ]
        ]);

        // Assign arg value to the OptimumAutomotiveOptionWithAuth instance

        if (count($args) > 0) {

            // check if first argument is an array

            if (!is_array($args[0])) {
                throw new \Exception("The argument must be an array");
            }

            foreach ($args[0] as $arg => $value) {

                // check if instance accepts this arg

                if (!property_exists($params, $arg)) {
                    throw new \Exception("Invalid argument '{$arg}'");
                }

                $params->$arg = $value;
            }
        }
        
        return call_user_func_array([$this->client, $method], [$params]);
    }

    /**
     * Set the user associated to the service
     */
    public function setAuth(string $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

}



/**
 * Optimum Automotive option
 * to get vehicles list
 */
class GetVehiculesList extends OptimumAutomotiveOptionWithAuth {}

/**
 * Optimum Automotive option
 * to get vehicles list
 */
class GetVehiculesListGlobal extends OptimumAutomotiveOptionWithAuth {}

/**
 * Optimum Automotive option
 * to get vehicules list
 */
class VehiculesListDetail extends OptimumAutomotiveOptionWithAuth {

    /**
     * Identifier of the vehicle queried
     */
    public string $idtracker;
    
    public float $cons_eu_t;
    public float $cons_ur_t;
    public int $tank;
}

/**
 * Only data that is not zero (or greater than 0) is actually updated, 
 * which allows partial updating of information.
 */
class UpdateVehicule extends OptimumAutomotiveOptionWithAuth {

    /**
     * Vehicle data to update. 
     * Only the fields specified above are considered
     */
    public VehiculesListDetail $vehiculeData;
}

/**
 * Synthetic instantaneous position 
 * of a vehicle
 */
class GetVehiculeLocation extends OptimumAutomotiveOptionWithAuth {
    
    /**
     * Registration of the vehicle queried
     */
    public string $immat;
}

/**
 * Synthetic instantaneous position 
 * of vehicles
 */
class GetVehiculesLocation extends OptimumAutomotiveOptionWithAuth {

    /**
     * Identifier of the vehicle queried
     */
    public string $immat;
}

/**
 * Detailed instantaneous position 
 * of a vehicle
 */
class GetPosition extends OptimumAutomotiveOptionWithAuth {

    /**
     * Identifier of the vehicle queried
     */
    public string $idtracker;
}

/**
 * History of the detailed positions 
 * of a vehicle over one day
 */
class GetPositions extends OptimumAutomotiveOptionWithAuth {

    /**
     * Identifier of the vehicle queried
     */
    public string $idtracker;

    /**
     * Date of the day queried (YYYYMMDD)
     */
    public string $ladate;

    /**
     * Number of positions requested 
     * (0 = all positions for the day)
     */
    public int $nbpos = 0;
}

/**
 * History of the detailed positions 
 * of a vehicle over one day
 */
class GetTrajets extends OptimumAutomotiveOptionWithAuth {

    /**
     * Identifier of the vehicle queried
     */
    public string $idtracker;

    /**
     * Date of the day queried (YYYYMMDD)
     */
    public string $ladate;
}

/**
 * This method provides the mileage data associated 
 * with the last known position of the vehicle
 */
class GetKms extends OptimumAutomotiveOptionWithAuth {

    /**
     * Registration of the vehicle queried
     */
    public string $immat;
}

/**
 * Fuel consumption over a period
 */
class GetFuelConsumption extends OptimumAutomotiveOptionWithAuth {

    /**
     * Registration of the vehicle queried
     */
    public string $immat;

    /**
     * Start date of the period surveyed
     */
    public string $periodStart;

    /**
     * End date of the period surveyed
     */
    public string $periodEnd;
}

/**
 * This method provides the mileage data as well as the fuel level when known.
 * The source of information is specified in each case in order to clearly identify these two data when available.
 */
class ReadVehicleOdometerAndTankInformation extends OptimumAutomotiveOptionWithAuth {

    /**
     * Identifier of the vehicle queried
     */
    public int $idTracker;
}

/**
 * This method makes it possible to activate a geofencing alert 
 * for a vehicle in one of the surveillance zones defined on the platform of the identified client. 
 * Zones must be created on the platform, and can only be activated by API, on their (exact) name.
 */
class SetAlertOut extends OptimumAutomotiveOptionWithAuth {

    /**
     * Identifier of the vehicle queried
     */
    public string $idTracker;

    /**
     * Name (in the platform) 
     * of the geofencing zone concerned
     */
    public string $locationName;

    /**
     * true to enable the alert, 
     * false to disable it
     */
    public bool $isActive;
}

/**
 * This method is used to activate an abnormal movement alert for a vehicle. 
 * This is an alert that is triggered as soon as the vehicle starts moving. 
 * It therefore targets vehicles for which any movement is prohibited. 
 * For vehicles that can only move in a limited area, use the geofencing alert activation method.
 */
class SetTimeMovementAlert extends OptimumAutomotiveOptionWithAuth {

    /**
     * Identifier of the vehicle queried
     */
    public string $idTracker;

    /**
     * true to enable the alert, 
     * false to disable it
     */
    public bool $isActive;
}

/**
 * Get the coordinates of an address
 */
class GetGeo extends OptimumAutomotiveOptionWithAuth {

    /**
     * Complete address 
     * (including postal code and city) 
     * to be geolocated
     */
    public string $adresse;
}

/**
 * Get the coordinates of an address
 */
class GetReverseGeo extends OptimumAutomotiveOptionWithAuth {

    /**
     * Get the address corresponding to coordinates
     */
    public string $LatLong;
}
