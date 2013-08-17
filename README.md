mrPictureSorter
===============

takes all pictures within a given folder and relocate them in the folder base on their exif information

This is based on Symfony2's Command, filesystem and finders components

Install
---------------

    git clone https://github.com/martinratinaud/mrPictureSorter
    composer install

Launch
---------------

    php bin/console sort /Users/darkvador/Pictures/PhotosATrier/

This will create a new *__DONE* folder with pictures sorted in a Ymd format which you can change by setting a format option

    php bin/console sort --format=Y-m-d /Users/darkvador/Pictures/PhotosATrier/

will format images this way

    __DONE
        2013-05-12
            xxx.png
            xxx2.png
            ...
        2013-06-13
            ...

