# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

  config.vm.box = "juremalovrh/phpbox"

  config.vm.provision "shell",
    inline: "mysql -uroot -proot < /vagrant/setup.sql && cd /vagrant && composer install"
end
