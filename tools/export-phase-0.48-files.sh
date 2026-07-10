#!/usr/bin/env sh
set -eu

# Export current authentication, session, routing, and DI wiring files needed
# for the full 2FA post-login redirect replacement phase.

OUTPUT="requested-files-phase-0.48.txt"
: > "$OUTPUT"

add_file() {
    file="$1"
    {
        printf '\n===== %s =====\n' "$file"
        if [ -f "$file" ]; then
            nl -ba "$file"
        else
            printf '[MISSING FILE] %s\n' "$file"
        fi
    } >> "$OUTPUT"
}

add_file "app/zoosper-core/src/Bootstrap/ApplicationFactory.php"
add_file "app/zoosper-core/src/Routing/ModuleRouteLoader.php"
add_file "app/zoosper-core/src/Routing/ControllerProviderLoader.php"
add_file "app/zoosper-auth/src/Controller/AuthController.php"
add_file "app/zoosper-auth/src/Service/AuthService.php"
add_file "app/zoosper-auth/src/Service/SessionGuard.php"
add_file "app/zoosper-auth/config/controllers.php"
add_file "app/zoosper-auth/config/admin_routes.php"
add_file "app/zoosper-auth/config/routes.php"
add_file "app/zoosper-two-factor/config/controllers.php"
add_file "app/zoosper-two-factor/config/admin_routes.php"
add_file "config/admin.php"
add_file "config/two_factor.php"

find app -type f \( -path "*/config/admin_routes.php" -o -path "*/config/routes.php" -o -path "*/config/controllers.php" \) | sort | while read -r file; do
    add_file "$file"
done

printf 'Exported to: %s\n' "$OUTPUT"
ls -lh "$OUTPUT"
