#!/bin/bash
cd /wgclients

# Read configured Wireguard Clients and write to file
xmllint --xpath /opnsense/OPNsense/wireguard/client/clients  /conf/config.xml > wgclients.xml
# Remove the <clients> tags from xml file
sed -i "" '/\<clients\>/d' wgclients.xml   
# Remove the whitespace from xml file
sed -i "" 's/^[ \t]*//;s/[ \t]*$//' wgclients.xml
# create single files from xml file
split -d -p '<client uuid' wgclients.xml wgconf
#csplit -k wgclients.xml '/<client uuid/' {100}

#get Public Interface and IP
REDIF=$(route get default | sed -n -e 's/^.*interface: //p')
REDIP=$(ifconfig $REDIF | grep "inet " | awk '{print $2}')

# Read Server Definition from XML File and save to file and extract variables
xmllint --xpath /opnsense/OPNsense/wireguard/server/servers /conf/config.xml > wgserver.xml
SRVPUBKEY=$(xmllint -xpath string'(servers/server/pubkey)' wgserver.xml)
SRVPORT=$(xmllint -xpath string'(servers/server/port)' wgserver.xml)
SRVENDPOINT=$REDIP:$SRVPORT

#echo Interface $REDIF IP $REDIP

for i in wgconf*
do
wgconfname=$(grep name $i | sed 's/<[^>]*>//g').conf;
mv "$i" "$wgconfname"
CLNTPUBKEY=$(xmllint -xpath string'(client/pubkey)' $wgconfname)
CLNTPRIVKEY=$(xmllint -xpath string'(client/privkey)' $wgconfname)
CLNTTUNADDRESS=$(xmllint -xpath string'(client/tunneladdress)' $wgconfname)
CLNTDNS=$(xmllint -xpath string'(client/dnsaddress)' $wgconfname)
CLNTALLOWEDIPS=0.0.0.0/0
# muss noch auf lan subnet ge√ndert werden CLNTALLOWEDIPS=$(xmllint -xpath string'(client/dnsaddress)' $wgconfname)
CLNTKEEPALIVE=$(xmllint -xpath string'(client/keepalive)' $wgconfname)

cat <<EOF > $wgconfname.new
[Interface]
# PublicKey = $CLNTPUBKEY
PrivateKey = $CLNTPRIVKEY
Address = $CLNTTUNADDRESS
DNS = $CLNTDNS

[Peer]
PublicKey = $SRVPUBKEY
AllowedIPs = $CLNTALLOWEDIPS
Endpoint = $SRVENDPOINT
EOF
done
