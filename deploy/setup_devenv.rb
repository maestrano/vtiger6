# Script to be ran as sudo
# Arguments 
# 0 : Container name [required]
# 1 : Path to the directory where to link the /vtigercrm folder [optional]
# ----------------------

require "json"

def get_ip_address(a_name)
  puts "Retrieving IP..."

  inspect_json = JSON.parse(`docker inspect #{a_name}`)
  if inspect_json.count > 0
    ip = inspect_json.first["NetworkSettings"]["IPAddress"]
    puts "...IP #{ip} found for container #{a_name}"
    return ip
  else
    puts "Error: Container #{a_name} not found"
    return nil
  end
end

def update_host(an_ip)
  puts "Updating host..."

  hosts_file = `sudo cat /etc/hosts`
  if !hosts_file.gsub!(/((?:\d{1,3}\.?){4})\svtiger6\.app\.dev\.maestrano\.io/, "#{an_ip} vtiger6.app.dev.maestrano.io")
    hosts_file << "\n"
    hosts_file << "# vTiger6 host - added by setup_devenv script\n"
    hosts_file << "#{an_ip} vtiger6.app.dev.maestrano.io\n"
  end
  File.open("/etc/hosts", 'w') { |file| file.write(hosts_file) }
  puts "...Host vtiger6.app.dev.maestrano.io updated with IP #{an_ip}"
end

def get_volume(a_name)
  puts "Retrieving app source volume..."

  inspect_json = JSON.parse(`docker inspect #{a_name}`)
  if inspect_json.count > 0
    volume = inspect_json.first["Volumes"]["/var/lib/vtigercrm"]
    if volume && !volume.empty?
      puts "...Volume #{volume} found for container #{a_name}"
      return volume
    else
      puts "Error: Volume /var/lib/vtigercrm not found on container"
      return nil
    end
  else
    puts "Error: Container #{a_name} not found"
    return nil
  end
end 

def link_volume(target,path)
  puts "Linking app source volume..."

  `sudo rm -rf #{path}`
  `mkdir -p #{path}`
  `ln -s #{target}/webapp #{path}`
  # Git will ignore permission changes
  `cd #{path}/webapp && git config --local core.filemode false`

  puts "...Work directory available in #{path}"
end


# Script start
# ----------------------

container_name = ARGV[0]
work_dir = ARGV[1] || "./vtiger6"

# Retrieves the container IP
if container_name && container_name.is_a?(String) && !container_name.strip.empty?
  container_ip = get_ip_address(container_name)
  
  #  Updates /etc/hosts
  if container_ip && container_ip.is_a?(String) && !container_ip.empty?
    update_host(container_ip)
    puts "Restarting Apache2..."
    puts `sudo service apache2 restart`
  else
    puts "Error: IP not found"
  end

  # Links the volume /var/lib/vtigercrm to a given path on the host
  if work_dir && work_dir.is_a?(String) && !work_dir.empty?
    mnt = get_volume(container_name)
    link_volume(mnt, work_dir) if mnt
  else
    puts "Work directory not specified"
  end

else
  puts "Error: Container not specified"
end
