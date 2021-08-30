# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  config.vm.box = "hashicorp/bionic64"
  config.vm.hostname = "lancache-autofill"
  config.vm.network "public_network",
      use_dhcp_assigned_default_route: true

    config.vm.provider "virtualbox" do |v| 
      v.customize ["modifyvm", :id, "--natdnshostresolver1", "off"]
      v.customize ["modifyvm", :id, "--natdnsproxy1", "off"]
      v.customize ["modifyvm", :id, "--uartmode1", "disconnected" ]
    end

    config.vm.provision "project",
    type: "shell",
    privileged: false,
    inline: "/vagrant/install.sh"

end
