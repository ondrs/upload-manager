makefile_path := $(abspath $(lastword $(MAKEFILE_LIST)))
makefile_dir := $(dir $(makefile_path))

build:
	docker build . --tag upload-manager:latest

install:
	docker run -it --rm --name upload-manager -v $(makefile_dir):/srv/app -w /srv/app upload-manager:latest composer install

bash:
	docker run -it --rm --name upload-manager -v $(makefile_dir):/srv/app -w /srv/app upload-manager:latest bash
	
test:
	docker run -it --rm --name upload-manager -v $(makefile_dir):/srv/app -w /srv/app upload-manager:latest /srv/app/vendor/bin/tester -c /srv/app/tests/php.ini -j 40 ./tests