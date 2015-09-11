# Upload

File uploads are nice. We were processing uploaded files in so many
applications, that it became easier to make a single, polymorphic relation for
reuse across applications and entities.


## What's in the box?

The Upload model is simple enough. It comes paired with traits, for both Models
and Repositories, to provide the necessary functionality to relate to uploads.


## Installation and setup

1. Add `"c4tech/upload": "1.x"` to your composer requirements and run `composer update`.
2. Add `C4tech\Upload\ServiceProvider` to your service providers config array.
3. Run `php artisan vendor:publish` to get the migrations.
4. Run `php artisan migrate` to set up themigrations.
5. Edit `config/foundation.php` and add an entry in `models` and `repos` for `upload`.
5. (Optional) Map the Repository Facades in your facades config array for fast access:
    a. `"Upload" => "C4tech\Upload\Facade"`
