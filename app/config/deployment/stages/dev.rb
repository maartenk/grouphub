server 'localhost', user: 'vagrant', roles: %w{web db app}

set :deploy_to, '/tmp/dev.grouphub.surfuni.org'
set :file_permissions_users, ["vagrant"]
