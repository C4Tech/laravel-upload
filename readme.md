# Upload

File uploads are nice. We were processing uploaded files in so many
applications, that it became easier to make a single, polymorphic relation for
reuse across applications and entities.

[![Latest Stable Version](https://poser.pugx.org/c4tech/upload/v/stable)](https://packagist.org/packages/c4tech/upload)
[![Build Status](https://travis-ci.org/C4Tech/laravel-upload.svg?branch=master)](https://travis-ci.org/C4Tech/laravel-upload)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/C4Tech/laravel-upload/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/C4Tech/laravel-upload/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/C4Tech/laravel-upload/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/C4Tech/laravel-upload/?branch=master)

## What's in the box?

The Upload model is simple enough. It comes paired with traits, for both Models
and Repositories, to provide the necessary functionality to relate to uploads.


## Installation and setup

1. Add `"c4tech/upload": "1.x"` to your composer requirements and run `composer update`.
2. Add `C4tech\Upload\ServiceProvider` to your service providers config array.
3. Run `php artisan vendor:publish` to get the migrations.
4. Run `php artisan migrate` to set up the migrations.
5. (Optional) Edit `config/upload.php` and change entries in `models` and `repos` to match your class names.
5. (Optional) Map the Repository Facades in your facades config array for fast access:
    a. `"Upload" => "C4tech\Upload\Facade"`
