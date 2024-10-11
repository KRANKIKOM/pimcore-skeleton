#!/bin/bash

random_pass() {
    if command -v pwgen > /dev/null; then
        PASS=$(pwgen 16 1 2>/dev/null)
    else
        PASS=$(openssl rand -hex 16)
    fi
    echo "$PASS"
}

replace_pattern() {
    file="$1"
    key="$2"
    start_delimiter="$3"
    end_delimiter="$4"

    new_value="$(random_pass)"
    echo "$key: $new_value"
    pattern="$key$start_delimiter.*$end_delimiter"
    replacement="$key$start_delimiter$new_value$end_delimiter"
    if [[ "$OSTYPE" == "darwin"* ]]; then
        sed -i '' -e "s/$pattern/$replacement/" "$file"
    else
        sed -i "s/$pattern/$replacement/" "$file"
    fi
}

for ENV in dev staging production; do
    echo ""
    echo "Environment: $ENV"

    if [ ! -d "$ENV" ]; then
        echo "Directory $ENV does not exist."
        continue
    fi

    replace_pattern "$ENV/config/kustomization.yaml" \
                    'mysql-root-password' \
                    '="' \
                    '"'

    replace_pattern "$ENV/config/kustomization.yaml" \
                    'mysql-password' \
                    '="' \
                    '"'

    replace_pattern "$ENV/config/config.env" \
                    'PIMCORE_SECRET' \
                    '=' \
                    ''

    replace_pattern "$ENV/config/config.env" \
                    'REDIS-PASSWORD' \
                    '=' \
                    ''
done
