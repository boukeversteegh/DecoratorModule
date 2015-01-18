# DecoratorModule

This module allows you to decorate classes with new functionality. You can create different modules that add methods, properties and interfaces to a class.


### Example

``entities.php``
```php
<?php
namespace Entity;

class User { public $name; }
class Post { public $text; }
```
``application.php``
```php
namespace Application;

\DecoratorModule\DecoratorManager::register();

# Create Aliases
$dm = \DecoratorModule\DecoratorManager::instance();
$dm->decorate('Decorated\Entity\User', 'Entity\User');
$dm->decorate('Decorated\Entity\Post', 'Entity\Post');

# Include other modules that decorate the entities
require 'loginmodule.php';
require 'socialmodule.php';

# Instantiate your entities using the alias, and use functionality added by the different modules.
$user = new \Decorated\Entity\User;
$user->name = "John";
$user->login('johndoe', 's3cret');

$post = new \Decorated\Entity\Post;
if( $post instanceof \SocialModule\LikeableInterface ) {
    $post->addLikedBy($user);
    $post->getLikes(); # 1
}
```

``loginmodule.php``

```php
namespace LoginModule;

trait CanLoginTrait {
    public function login($username, $password) {
        /* login code */
    }
}

$dm = DecoratorModule\DecoratorManager::instance();
$dm->decorate('Decorated\Entity\User')
    ->use('\LoginModule\CanLoginTrait');

```

### Add liking to your application

``socialmodule.php``
```php
<?php
namespace SocialModule;

interface LikeableInterface {
    public function getLikes();
    public function addLikedBy(\Entity\User $user);
    public function getLikedBy(\Entity\User $user);
}

trait LikeableTrait {
    protected $likes=0;
    protected $likedBy=[];
    
    public function addLikedBy(\Entity\User $user) {
        $this->likes++;
        $this->likedBy[] = $user;
    }
    
    public function getLikes() {
        return $this->likes;
    }
    
    public function getLikedBy() {
        return $this->likedBy;
    }
}

$dm = new \DecoratorModule\DecoratorManager::instance();

# Posts can be liked
$dm->decorate('Decorated\Entity\Post', 'Entity\Post')
    ->use('\SharingModule\LikeableTrait')
    ->implements('\SharingModule\LikeableInterface');
```
