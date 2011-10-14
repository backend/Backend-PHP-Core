Backend-Core
============

Backend-Core is a basic code base that provides REST functionality using MVC architecture.

It also has the following advantages:

* Unit Testable and Tested using PHPUnit
* ZEND Coding Standards compliant
* Can be called using the command line or a web client (like a browser)

It serves as the ideal low level base for applications or frameworks that need to be
RESTful and done using MVC.

Installation
----------

    git clone git@github.com:jrgns/backend-core.git

Usage
----

###Command Line

    cd public
    php index.php GET contacts #Will run contacts/read/0
    php index.php DELETE contacts/3 #Will run contacts/delete/3
    php index.php GET contacts/3 json #Will run contacts/delete/3 with the JsonView

###HTTP

    curl --data contacts --data _method=POST http://localhost/backend-core/public/index.php #Will run contacts/create
      #Will run contacts/update/3
    curl --data contacts/3 http://localhost/backend-core/public/index.php  #Will run contacts/read/3

Details
------

RESTfullnes is achieved in the Web environment by checking the following for the HTTP
verb to use:

* A `_method` POST variable
* A `X_HTTP_METHOD_OVERRIDE` header sent with the request
* The HTTP request's method

The MVC components are structured as follows:

* All Application Logic happens in the Controller
* All Business Logic happens in the Model
* All Output happens in the View

The Application class holds everything together.
