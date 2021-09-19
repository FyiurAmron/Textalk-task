Image CAche & Reformat
======================

Overview
--------

An Symfony-based image reformatter/cacher. A path to the remote image CDN
has to be set as `REMOTE_URL` in `app/.env` file. Requests are accepted through
`GET` requests following this pattern:

    `http://localhost:8080/get/remotePathFromRoot.ext?h=120&w=240&ext=webp`

e.g., with default remote URL set to `https://www.textalk.se/` :

    `http://localhost:8080/get/media/assets_textalk_se_media/images/m_iphone.png?h=120&w=240&ext=webp`

The `X-Cache` response header will be set to either `HIT` or `MISS`, depending on
actual cache state. Currently the cache is based on `memcached`, with all the
pros and cons related to using it.

Installation and running the application
----------------------------------------

You can install application on your local environment or using Docker.

### Using Docker

You need to have Docker and `docker-compose` installed on your local machine.

1. run `docker-compose up`.
2. application will be accessible at the following address: `http://localhost:8080/`.
3. Composer is accessible via `docker exec -it <PHP_FPM_CONTAINER_NAME> composer` etc.
4. actual `<PHP_FPM_CONTAINER_NAME>` can be checked by running `docker ps`
