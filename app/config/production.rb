set :domain,      "emokymai"
set :deploy_to,   "/home/#{domain}"
set :app_path,    "app"
set :web_path,    "web"
set :server_ip,   "88.119.151.44"
set :user,        "emokymai"
set :group,       "emokymai"
set :password,    "elrnggin11"
set :port,        "2241"
set :pty,         true


server server_ip, :app, :web, :primary => true
