Vagrant.configure("2") do |config|
    config.vm.box = "precise64"
    config.vm.synced_folder "srv/", "/srv/"
    #config.vm.provider "virtualbox" do |v|
    #    v.gui = true
    #    v.customize ["modifyvm", :id, "--cpus", "1"]
    #    v.customize ["modifyvm", :id, "--pae", "off"]
    #    v.customize ["modifyvm", :id, "--hwvirtex", "off"]
    #end
    config.vm.provision :salt do |salt|
        salt.minion_config = "srv/minion"
        salt.run_highstate = true
    end
end

Vagrant::Config.run do |config|
    config.vm.forward_port 80, 10080
    config.vm.forward_port 3000, 13000
end

