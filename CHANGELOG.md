# APIx-log changelog

#### Version 1.0.2 (10-Jun-2015)
- Added the logged message can be the context directly i.e. not a string. 
- Added `\InvalidArgumentException` with an explicite message to the main constructor.
- Added handling of Exception as context e.g. `$logger()->critical( new \Exception('Boo!') )`. 

#### Version 1.0.1 (9-Jun-2015)
- Added Scrutinizer checks.
- Added `.gitattributes` file.
- Added a unit tests `bootstrap.php` file.
- Added a default timezone to the unit tests bootstraper.
- Fixed the context array handler (convert data to JSON). 
- Added additional tests and minor changes.
- Updated the examples in `README.md`.
- Added a `CHANGELOG.md` file.

#### Version 1.0.0 (30-Sept-2014)
- Initial release.


<pre>
  _|_|    _|_|    _|     _|      _|
_|    _| _|    _|         _|    _|
_|    _| _|    _| _|        _|_|
_|_|_|_| _|_|_|   _| _|_|   _|_|
_|    _| _|       _|      _|    _|
_|    _| _|       _|     _|      _|
</pre>