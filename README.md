ZF2-MongoDB-VirtualCollections 0.4.1
====================================

Zend Framework 2 Module for MongoDB operations abstraction.

Master: [![Build Status](https://travis-ci.org/pruno/ZF2-MongoDB-VirtualCollections.png?branch=master)](https://travis-ci.org/pruno/ZF2-MongoDB-VirtualCollections)
Develop: [![Build Status](https://travis-ci.org/pruno/ZF2-MongoDB-VirtualCollections.png?branch=develop)](https://travis-ci.org/pruno/ZF2-MongoDB-VirtualCollections)



Introduction
------------

This Module aims to provide:

 - An handy Service Abstract Factory for \MongoDB instances
 - A simple Collection/Object extensible and hydratable user-space abstraction
 - A pattern to represent a unique mongo collection as more user-space collections (follow-up to know more)
 
 
About Virtual Collections
-------------------

Virtualising multiple collections on top of a single real one the application is able (in addition to all the usual actions) to perform "Agnostic" queries.  
Perform Agnostic query means: querying the database (with or without a criteria set) for one or more object without knowing their kind.

Consider the following scenario:

    1# A client request the object with id 527cacf1cfdfc0fd308b4583 using the route /:id
    2# The application has no information regarding the desired object type.
       on a regular base you may have a route similar to /burritos/:id that will tell the application
       to search for the given id inside the burritos collection.
    3# With collection virtualization the database could be queried from a support collection (the 
       representation of the real mongo collection) and get back a working instance of the model 
       class Burrito.

This pattern is also good to easily extend controller logic alongside the model structure

Here's another scenario:

    # At a certain point in an application lifecycle the definition the objects SpicyBurrito and 
      VeganBurrito is required.
    # They both clearly derive from the Burrito object, yet they must define 2 completely different
      sets of attributes.
    # Both Objects and Collections declaration of the new entities could extends from the burrito's ones
      instead of defining a single object with huge sets of attributes (most of which will be usually
      empty), still maintaining the basic mvc route /:id
      


Installation
------------

Using [composer](http://getcomposer.org/):

Add the following to your `composer.json` file:

    "require": {
        ...
        "pruno/ZF2-MongoDB-VirtualCollections": ">=0.2.0",
    },
    ...
    "repositories": [
        ...
        {
            "type": "vcs",
            "url": "https://github.com/pruno/ZF2-MongoDB-VirtualCollections.git"
        }
    ]

Alternately with git submodules:

    git submodule add https://github.com/pruno/ZF2-MongoDB-VirtualCollections.git pruno/ZF2-MongoDB-VirtualCollections

 
How To's
------

Preface: This module doesn't provide any concrete class. You must declare all of your classes extending from the provided abstracts.  
Every Abstract class may declare some abstract functions, those will need to defined in order to provide the information needed from the module to work properly.


### MongoDbAbstractServiceFactory

Register the factory under the ServiceManager than configure as follow:

    'mongodb_virtual_collections' => array(
        'drivers' => array(
            'myDriver1Alias' => array(
                'hosts' => 'mo1.my.com:27017,mo2.my.com:27018',     // Comma separated list of hosts
                'database' => 'MongoDbVirtualCollectionsTest',
                'username' => 'pruno',                              // Optional
                'password' => 'supersecrepasswd',                   // Optional
                'options' => array(
                    // Driver options like replica_set goes here
                )
            ),
            ...
        )
    )
    
The ServiceManager will return an instance of \MongoDB

    $serviceManager->get('myDriver1Alias');


### Collections (simple)

In order to declare a collection you need to extend from AbstractCollection, which require a \MongoDB instance.

    $burritosCollection = new BurritosCollection($serviceManager->get('myDriver1Alias'));
    
What you must to declare in your class:

1 - The name you want to assign it on the database

    /**
     * @var string
     */
    protected $collectionName = 'burritos';

2 - The method declaring the object prototype associated with the collection

    /**
     * @return Burrito
     */
    public function createObjectPrototype()
    {
        return new Burrito($this);
    }
    
    
### Objects 

In order to declare an object you need to extend from AbstractObject, which require an AbstractCollection instance.

What you must to declare in your class:  

An arbitrary number of public attribute (those will describe your object).  
The _id property is already declared in the parent.  
Be aware: you can't set attribute at runtime if not declared inside the class, such limitation is useful to guarantee structure consistency. 

    class Burrito extends AbstractObject
    {
        /**
         * @var string
         */
        public $nameOnMenu;

        /**
         * @var int
         */
        public $price;

        …
    }
 
 
### Support collections

By their self useless, yet fundamental to let the VirtualCollection works.  
SupportCollections are the user-space representation of the real collections on the database while working in a virtual environment.  
They should never declare their own object prototype since they don't represent a set similar objects.  
AbstractSupportCollection (which you must extend) directly extend from AbstractCollection so, like previously, you must define the $collectionName private property.


### CollectionAbstractFactory

With this factory both simple and support collection can be created.  
This factory requires that you'r using also MongoDbAbstractServiceFactory to create the drivers.  
Register the factory under the ServiceManager than configure as follow:

    'mongodb_virtual_collections' => array(
        'collections' => array(
            // Class FQN                           => driver name
            'Application\Model\BurritosCollection' => 'myDriver1Alias', 
            'Application\Model\SupportCollection'  => 'myDriver1Alias', 
            …
        ),
    )
    
The ServiceManager will return an instance of the collection

    $serviceManager->get('Application\Model\BurritosCollection');
    
    
### Virtual collections

Virtual collections covers in what support collections lack of, describing a set of similar object, but they are not able to represent the real database collection.  
AbstractVirtualCollection (which you must extend) directly extend from AbstractCollection and depends on an AbstractSupportCollection

    $burrito = new Burrito($serviceManager->get('Application\Model\SupportCollection'));

Virtual Collections must declare their own object prototype (refer to the simple collection section).  

#### Aliasing

When storing an object belonging to a virtual collection the module will add an hidden attribute to the real database object identifying at which collection the object is associated.  
IMPORTANT: By default the collection class full qualified name is used, however this behaviour is discouraged, because a slightly change in your application code structure may compromise your pre-existing data. In order to avoid this, virtual collections class names can be internally aliased, for you is simple as declaring the ALIAS constant in the collection.

    /**
     * @var string
     */
    const ALIAS = 'burritos';


### VirtualCollectionAbstractFactory

This factory requires that your using also MongoDbAbstractServiceFactory to create the drivers and CollectionAbstractFactory to create support collections.  
Register the factory under the ServiceManager than configure as follow:

    'mongodb_virtual_collections' => array(
        'virtual_collections' => array(
            // Class FQN                           => support collection alias
            'Application\Model\BurritosCollection' => 'Application\Model\SupportCollection', 
            ...
        ),
    )
    
The ServiceManager will return an instance of the collection

    $serviceManager->get('Application\Model\BurritosCollection');
    

### Perform an agnostic query

In order to perform an agnostic query the Support Collection must be used.

    $serviceManager->get('Application\Model\BurritosCollection');
    $serviceManager->get('Application\Model\SpaceShuttlesCollection');
    $object = $serviceManager->get('Application\Model\SupportCollection')->findById($id);
    
Supported agnostic methods are:

- find()
- findOne()
- findById()
    
IMPORTANT:  
To achieve this result, the support collection resolve virtual collections aliases to their class. In order to do this, those collections must be previously registered (at runtime) to the support collection.  
Internally, a virtual collection register itself at creation, so: you must guarantee that all the virtual collection in which the object may be contained have been registered before attempting to perform an agnostic query. If not, an exception will be throned due to the fact that the support collection ignore which class should represent the fetched data.  


#### Virtual Collection registration alternatives

As stated above the simplest way of registering a virtual collection is instantiating it. For large code-base, registering all the model by initialisation can result in a useless waste of memory, cpu and time.  
Using the method  

    AbstractSupportCollection::registerVirtualCollection(string $alias, mixed $virtualCollection)  
    
it's possible to register a collection in though the following methods:

    - An instance of the collection (hardly useful, the collection register itself upon creation).
    - A factory implementing Zend\ServiceManager\FactoryInterface.
    - A Closure.
    - A string representing the service locator alias to whom the collection or a factory is registered.
    
Except for the first method all of those will request / initialise the desired collection with lazy loading.  



#### Virtualization group

Virtualization grouping is a functionality useful to perform agnostic operation only on a subset of registered virtual collections.  

    $serviceManager->get('Application\Model\BurritosCollection');
    $serviceManager->get('Application\Model\SpaceShuttlesCollection');
    $serviceManager->get('Application\Model\ChairsCollection');
    $supportCollection = $serviceManager->get('Application\Model\SupportCollection');
    
    $supportCollection->find(); // Will search for Burritos, SpaseShuttles and Chairs.
    
    $supportCollection->setVirtualizationGroup(array('burritos', 'chairs')); // Those are the collections aliases.
    
    $supportCollection->find(); // Will not search SpaseShuttles.
    
    $supportCollection->setVirtualizationGroup(array()); // Restore default functionality.


### Bundled hydrator

Every collection comes with it's own Hydrator (\Zend\Stdlib\Hydrator\ArraySerializable), internally it's used to hydrate and extract data from and to objects and the php native mongo driver.  
By default one strategy is registered (MongoIdStrategy) and is responsible for the conversion from e to an id string and the native object \MongoId.  
This functionality is easily extensible overriding the protected method AbstractCollection::createHydrator().  
Be careful: in order to grant the major number of functionality of the collection the MongoIdStrategy must be set, you should either invoke the parent method or provide it by your own.  

    /**
     * @return ArraySerializable
     */
    protected function createHydrator()
    {
        $hydrator = parent::createHydrator();
        …

        return $hydrator;
    }


License
-------

This software is released under the BSD-3-Clause License.


How to contribute
-----------------
 
Please do. Fork it and send pull requests or open an issue if required.