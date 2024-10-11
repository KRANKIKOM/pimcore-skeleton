#!/bin/bash

ensure_delimiter() {
    local sql_file=$1
    local temp_file=$(mktemp)
    cat "$sql_file" > "$temp_file"
    echo "//" >> "$temp_file"
    echo "$temp_file"
}

echo ""
/usr/games/cowsay "Checking pimcore bundles"
echo ""

bin/console pimcore:bundle:list --json 2>/dev/null | grep -v Deprecat | jq -c '.[]'  | while read i; do 
    #echo $i;
    bundle=$(echo $i | jq -r '.Bundle')
    installable=$(echo $i | jq -r '.Installable')
    installed=$(echo $i | jq -r '.Installed')
    echo "Bundle: $bundle Installable: $installable Installed: $installed"
    if [ "$installable" = "true" ]; then
        if [ "$installed" = "false" ]; then
            echo "Bundle $bundle needs to be installed"
            ./bin/console pimcore:bundle:install "$bundle" --no-assets-install --no-cache-clear -n
            bundle_installed=true
        else
            echo "Bundle $bundle already installed" 
        fi
    fi     

    if [ "$bundle" = "PimcoreCustomerManagementFrameworkBundle" ]; then

        # Set default values if not already set
        MYSQL_HOST="${MYSQL_HOST:-db}"
        MYSQL_PORT="${MYSQL_PORT:-3306}"
        MYSQL_DB="${MYSQL_DB:-pimcore}"
        MYSQL_USER="${MYSQL_USER:-pimcore}"
        MYSQL_PASSWORD="${MYSQL_PASSWORD:-pimcore}"
        
        # Override with PIMCORE_INSTALL values if they exist
        MYSQL_USER="${PIMCORE_INSTALL_MYSQL_USERNAME:-$MYSQL_USER}"
        MYSQL_PASSWORD="${PIMCORE_INSTALL_MYSQL_PASSWORD:-$MYSQL_PASSWORD}"
        
        # Command to connect to MySQL
        MYSQL_CMD="mysql -u \"$MYSQL_USER\" --password=\"$MYSQL_PASSWORD\" -h \"$MYSQL_HOST\" -P $MYSQL_PORT $MYSQL_DB"
        
        for sqlFile in $(find /var/www/html/vendor/pimcore/customer-management-framework-bundle/src/Resources/sql/ -name "stored*.sql"); do 
            echo "Processing SQL File: $sqlFile"
            
            # Create a temporary file
            TMP_FILE=$(mktemp)
        
            # Extract the DROP FUNCTION line and directly append it to the temp file
            grep -i "^DROP FUNCTION" "$sqlFile" > "$TMP_FILE"
        
            # Append custom delimiter setting, the CREATE FUNCTION block (excluding the DROP line), and delimiter reset
            echo "DELIMITER \$\$" >> "$TMP_FILE"
            sed '1d' "$sqlFile" >> "$TMP_FILE" # Assuming DROP FUNCTION is always the first line
            echo "\$\$" >> "$TMP_FILE"
            echo "DELIMITER ;" >> "$TMP_FILE"
            
            # Execute the modified SQL content
            cat "$TMP_FILE" | eval $MYSQL_CMD
            
            # Clean up the temporary file
            rm "$TMP_FILE"
        done
    fi
done

if [ "$bundle_installed" = true ]; then
    /usr/games/cowsay "Installing assets"
    bin/console assets:install
fi

echo ""
echo "-------------------------------------------------------------------------"
echo ""
