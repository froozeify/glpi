#!/bin/bash -eu

#
# ---------------------------------------------------------------------
#
# GLPI - Gestionnaire Libre de Parc Informatique
#
# http://glpi-project.org
#
# @copyright 2015-2026 Teclib' and contributors.
# @copyright 2003-2014 by the INDEPNET Development Team.
# @licence   https://www.gnu.org/licenses/gpl-3.0.html
#
# ---------------------------------------------------------------------
#
# LICENSE
#
# This file is part of GLPI.
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <https://www.gnu.org/licenses/>.
#
# ---------------------------------------------------------------------
#

# Nightly builds are now published as GitHub release assets (see nightly_build.yml) rather
# than committed to this repository, so this script no longer inspects local tarballs/git log.
# It records one row per branch in a small state file, then regenerates the index page from it.
#
# Usage: github-nightly-description.sh <data-file> <index-file> <version> <download-url> <size-bytes>

if [ "$#" -ne 5 ]
then
    echo "Usage: $0 <data-file> <index-file> <version> <download-url> <size-bytes>"
    exit 1
fi

DATA_FILE=$1
INDEX_FILE=$2
VERSION=$3
URL=$4
SIZE=$5
DATE=$(date -u +"%F %T UTC")

# Upsert the row for $VERSION (tab-separated: version, url, date, size), keep it sorted.
touch "$DATA_FILE"
grep -v -P "^${VERSION}\t" "$DATA_FILE" > "$DATA_FILE.tmp" || true
printf '%s\t%s\t%s\t%s\n' "$VERSION" "$URL" "$DATE" "$SIZE" >> "$DATA_FILE.tmp"
sort -o "$DATA_FILE" "$DATA_FILE.tmp"
rm -f "$DATA_FILE.tmp"

{
    cat <<HEADER
---
layout: default
title: GLPI Nightly Builds
---

Version|Archive|Build date|Size
---|---|---|---
HEADER

    while IFS=$'\t' read -r row_version row_url row_date row_size
    do
        FILENAME="${row_url##*/}"
        echo "$row_version|[$FILENAME]($row_url)|$row_date|$row_size"
    done < "$DATA_FILE"

    cat <<FOOTER

<font size="1">Page generated on $( date -u +'%F %H:%M:%S UTC' )</font>
FOOTER
} > "$INDEX_FILE"
