#!/bin/bash
# 
# CEM - Combined Events Monitor
# Startup Script - Create and archive exsisting data and start several processes that are necessary
# Set DSD+ Output to unused Sink to ensure  no interference from input 
# rtl_list is an extension of rtl_433 source to list device index by serial number
# 

# This is the parent folder which contains amalagted data from external applications
# Any directories created in a subdirectory called Recordings will automatically be created as an array by CEM
DSD_Directory="/home/sdr/Desktop/DSDPlus v2.268"
CEM_Directory="/home/sdr/Desktop/CEM"
Archive_Directory="/home/sdr/Desktop/Old Backups"
Archive_Name="Archive - $(date +"%m-%d-%Y %H-%M-%S")"

# bin directory
BIN_Directory="$( cd "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"

# Copy everything from CEM_Directory to an archive folder
echo "\n\n- Backing Up Data in $CEM_Directory to $Archive_Directory/$Archive_Name\n- This may take a while based on how much data is transfered, hardware and configuration\n\n"
mkdir "$Archive_Directory/$Archive_Name/"
cp -r "$CEM_Directory/" "$Archive_Directory/$Archive_Name/"
# Remove old Files
find "$CEM_Directory" -name \* -type f -delete
echo "Backup Complete"

cd "$CEM_Data_Directory"

# 
# DSDPlus
# 

# DSDPlus - 153.920
export PULSE_SINK="Virtual_Sink1" 
deviceindex=`$BIN_Directory/rtl_list -s 00000001`
gnome-terminal --tab --title="rtl_fm ( 153.920 MHz )" -- /bin/bash -c "rtl_fm -d $deviceindex -M fm -f 153.920M -s 12k -g 30 | play -r 12k -t raw -e s -b 16 -c 1 -V1 -; read"
export PULSE_SOURCE="Virtual_Sink1.monitor"
export PULSE_SINK="Virtual_Sink4" 
gnome-terminal --tab --title="DSDPlus ( 153.920 MHz )" -- /bin/bash -c "cd '$DSD_Directory'; wine DSDPlus.exe -F1 -rv -fr -Pwav; read"

sleep 6

# DSDPlus - 159.225
export PULSE_SINK="Virtual_Sink2" 
deviceindex=`$BIN_Directory/rtl_list -s 00000002`
gnome-terminal --tab --title="rtl_fm ( 159.225 MHz )" -- /bin/bash -c "rtl_fm -d $deviceindex -M fm -f 159.225M -s 12k -g 30 | play -r 12k -t raw -e s -b 16 -c 1 -V1 -; read"
export PULSE_SOURCE="Virtual_Sink2.monitor" 
export PULSE_SINK="Virtual_Sink4" 
gnome-terminal --tab --title="DSDPlus ( 159.225 MHz )" -- /bin/bash -c "cd '$DSD_Directory'; wine DSDPlus.exe -F2 -rv -fr -Pwav; read"


sleep 6


# 
# rtl_fm
# 

# rtl_fm - CN Rail
deviceindex=`$BIN_Directory/rtl_list -s 00000003`
gnome-terminal --tab --title="CN Rail (Edson)" -- /bin/bash -c "cd '$CEM_Directory/Recordings/CN/' && rtl_fm -d $deviceindex -s 12k -A fast -g 30 -l 35 -f 160.785M -f 161.195M -f 161.205M -f 161.415M -f 161.535M | sox --norm=-0 -t raw -r 12k -e signed -b 16 -c 1 -V1 - ".wav" silence 1 0.5 1% 1 0.5 1% : newfile : restart; read"

sleep 6

# rtl_fm - CYET Airport
deviceindex=`$BIN_Directory/rtl_list -s 00000004`
gnome-terminal --tab --title="CYET Airport" -- /bin/bash -c "cd '$CEM_Directory/Recordings/CYET/' && rtl_fm -d $deviceindex -s 12k -A fast -g 30 -l 35 -M am -f 123.200M | sox --norm=-0 -t raw -r 12k -e signed -b 16 -c 1 -V1 - ".wav" silence 1 0.5 1% 1 0.5 1% : newfile : restart; read"

sleep 6


# 
# rtl_433
# 

# rtl_433 - 315 MHz
deviceindex=`$BIN_Directory/rtl_list -s 00000005`
gnome-terminal --tab --title="rtl_433 345 MHz" -- /bin/bash -c "cd '$CEM_Directory' && rtl_433 -d $deviceindex -f 345000000 -G 4 -v -M level -F json:rtl_345.json; read"

sleep 6

# rtl_433 - 433.920 MHz
deviceindex=`$BIN_Directory/rtl_list -s 00000006`
#  -f 433920000
gnome-terminal --tab --title="rtl_433 433 MHz" -- /bin/bash -c "cd '$CEM_Directory' && rtl_433 -d $deviceindex -G 4 -v -M level -F json:rtl_433.json; read"

sleep 6

# Web Socket
gnome-terminal --tab --title="CEM Socket" -- /bin/bash -c "php /var/www/html/bin/server.php; read"



# Blank Tab for nicenesszz
# gnome-terminal --tab --title="Blank Tab" -- /bin/bash -c 'read'
