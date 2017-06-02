#!/bin/sh
set -xe

# Start Apache with the right permissions
$(dirname "$0")/start_safe_perms -DFOREGROUND
