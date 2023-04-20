<?php
namespace Deployer;

require 'recipe/symfony.php';

// User chmod whie we learn about acl.
set('writable_mode', 'acl'); // chmod, chown, chgrp or acl.

// Project name
set('application', 'servicios.publicos');

// Project repository
set('repository', 'git@github.com:RedCiudadana/ServiciosPublicosAdmin.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys 
add('shared_files', []);
add('shared_dirs', ['public/images']);

// Writable dirs by web server 
add('writable_dirs', ['public/images', 'node_modules']);


// Hosts

host('servicios-ocean')
    ->setHostname('142.93.77.143')
    ->setRemoteUser('redciudadana')
    ->set('remote_user', 'redciudadana')
    ->set('deploy_path', '/srv/web-apps/admin.tramites.redciudadana.org');

host('test-servicios-ocean')
    ->setHostname('164.92.136.254')
    ->setRemoteUser('redciudadana')
    ->set('remote_user', 'redciudadana')
    ->set('branch', 'feature/rutas_servicio')
    ->set('composer_options', '--verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader --ignore-platform-req=php')
    ->set('deploy_path', '/srv/web-apps/test.admin.tramites.gob.gt');

// Tasks

task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');


// Javascript deployment
after('deploy:vendors', 'deploy:build_vendors_js');

task('deploy:build_vendors_js', function () {
    runLocally('npm install');
    runLocally('npm build');
});

after('deploy:build_vendors_js', 'deploy:upload_vendors_js');

task('deploy:upload_vendors_js', function () {
    upload('public/build/', '{{release_path}}/public/build/');
});

// Migrate database before symlink new release.

// PENDIENTE CORREGIR, LA BASE DE datos de graph deberia estar en otra schema o corregir los problemas de tipeados de tenerlos mezclados
// before('deploy:symlink', 'database:migrate');

# move to migrations files onegai
task('database:migrate', function() {
    $options = '--force --dump-sql';
    run(sprintf('{{bin/console}} doctrine:schema:update %s {{console_options}}', $options));
});
