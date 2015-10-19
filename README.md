# redis-copy
very simple script that copies content of one redis server to another, using `predis` library

# Usage
* clone repository
* run composer.install
* tweak parameters in the `redis-copy.php` file (it is set to ignore specific keys)
* run `php redis-copy.php tcp://source.redis:6379 tcp://destination.redis:6379`
