# Stages
set :stages,       %w{production}
set :default_stage, "production"
set :stage_dir,    "app/config"
require 'capistrano/ext/multistage'

set :scm,         :git
# Or: `accurev`, `bzr`, `cvs`, `darcs`, `subversion`, `mercurial`, `perforce`, or `none`
set :repository,  "file:///var/www/emokymai"
set :deploy_via,  :copy
set :copy_exclude, [".git/*", ".svn/*", ".swp", ".DS_Store"]

set :model_manager, "doctrine"
# Or: `propel`

set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,   [app_path + "/logs", "uploads", "web/uploads", "vendor"]

set :use_composer, true
set :update_vendors, false


#role :web,        server_ip                         # Your HTTP server, Apache/etc
#role :app,        server_ip, :primary => true       # This may be the same as your `Web` server
#role :db,         server_ip, :primary => true

set  :keep_releases,  3
set  :use_sudo,   false

set :writable_dirs,       ["app/cache", "app/cache/prod", "app/logs"]
set :webserver_user,      "apache"
set :permission_method,   :acl
set :use_sudo,            true
set :use_set_permissions, true

default_run_options[:pty] = true

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL

task :upload_parameters do
  origin_file = "app/config/parameters_prod.yml"
  destination_file = shared_path + "/app/config/parameters.yml" # Notice the shared_path

  try_sudo "mkdir -p #{File.dirname(destination_file)}"
  top.upload(origin_file, destination_file)
end

task :after_deploy do
    console_options = "--env=prod"
    try_sudo "sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} assets:install #{console_options}'"
    try_sudo "sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} assetic:dump #{console_options}'"
    try_sudo "sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} doctrine:schema:update --force #{console_options}'"

    web_uploads_folder = latest_release + "/web/uploads"
    try_sudo "chown -h #{user}:#{group} #{web_uploads_folder}"
end

after "deploy:setup", "upload_parameters"

after "deploy:restart", "deploy:cleanup" 

after 'deploy',   'after_deploy'

