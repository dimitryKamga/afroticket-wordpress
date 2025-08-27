#!/bin/bash

# AfroTicket WordPress Release Manager
# Complete release workflow with version bumping, changelog, and deployment

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"
VERSION_FILE="$PROJECT_DIR/VERSION"
CHANGELOG_FILE="$PROJECT_DIR/CHANGELOG.md"

# Helper functions
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

log_step() {
    echo -e "${PURPLE}🚀 $1${NC}"
}

# Show usage information
show_usage() {
    cat << EOF
Usage: $0 [patch|minor|major] [OPTIONS]

Complete release workflow for AfroTicket WordPress project

Arguments:
  patch    Bug fixes and minor improvements (1.1.0 → 1.1.1)
  minor    New features and enhancements (1.1.0 → 1.2.0)
  major    Breaking changes (1.1.0 → 2.0.0)

Options:
  -m, --message MESSAGE    Custom release message
  -d, --dry-run           Preview changes without executing
  -s, --skip-tests        Skip pre-release testing
  -f, --force             Force release even with uncommitted changes
  --no-push              Don't push to remote repository
  --no-tag               Don't create git tags
  --no-changelog         Don't update changelog
  -h, --help             Show this help message

Release Process:
  1. 🔍 Pre-release validation
  2. 📝 Generate changelog from commits
  3. 🔢 Bump version numbers
  4. 🏷️ Create git tag
  5. 📤 Push to GitHub (triggers webhook deployment)
  6. ✅ Verify deployment

Examples:
  $0 patch                           # Standard patch release
  $0 minor -m "Add SMS system"       # Minor release with message
  $0 major --dry-run                # Preview major release
  $0 patch --no-push                # Local release only

EOF
}

# Validate environment
validate_environment() {
    log_step "Validating environment..."
    
    # Check if in git repository
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        log_error "Not in a git repository"
        return 1
    fi
    
    # Check for required files
    if [[ ! -f "$SCRIPT_DIR/bump-version.sh" ]]; then
        log_error "bump-version.sh script not found"
        return 1
    fi
    
    if [[ ! -f "$SCRIPT_DIR/update-changelog.sh" ]]; then
        log_error "update-changelog.sh script not found"
        return 1
    fi
    
    # Check git configuration
    if [[ -z "$(git config user.email)" ]] || [[ -z "$(git config user.name)" ]]; then
        log_error "Git user configuration missing. Set git user.name and user.email"
        return 1
    fi
    
    log_success "Environment validation passed"
    return 0
}

# Check for uncommitted changes
check_working_tree() {
    local force_mode="$1"
    
    if [[ -n $(git status --porcelain) ]]; then
        if [[ "$force_mode" == "true" ]]; then
            log_warning "Uncommitted changes detected, but continuing with --force"
            git status --short
        else
            log_error "Uncommitted changes detected. Commit or stash them first, or use --force"
            git status --short
            return 1
        fi
    fi
    
    return 0
}

# Run pre-release tests
run_tests() {
    log_step "Running pre-release tests..."
    
    # Check PHP syntax in theme files
    local theme_dir="$PROJECT_DIR/wp-content/themes/meup-child"
    if [[ -d "$theme_dir" ]]; then
        log_info "Checking PHP syntax in theme files..."
        find "$theme_dir" -name "*.php" -exec php -l {} \; > /dev/null
        log_success "PHP syntax check passed"
    fi
    
    # Validate version file format
    if [[ -f "$VERSION_FILE" ]]; then
        local version=$(cat "$VERSION_FILE")
        if [[ ! $version =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
            log_error "Invalid version format in VERSION file: $version"
            return 1
        fi
        log_success "Version file format validated"
    fi
    
    # Check changelog format
    if [[ -f "$CHANGELOG_FILE" ]]; then
        if ! grep -q "# Changelog" "$CHANGELOG_FILE"; then
            log_error "Invalid changelog format - missing '# Changelog' header"
            return 1
        fi
        log_success "Changelog format validated"
    fi
    
    log_success "All pre-release tests passed"
    return 0
}

# Get current version
get_current_version() {
    if [[ -f "$VERSION_FILE" ]]; then
        cat "$VERSION_FILE"
    else
        echo "1.0.0"
    fi
}

# Generate release summary
generate_release_summary() {
    local old_version="$1"
    local new_version="$2"
    local release_message="$3"
    
    cat << EOF

=====================================
📦 RELEASE SUMMARY
=====================================
Version: $old_version → $new_version
Date: $(date '+%Y-%m-%d %H:%M:%S')
Message: $release_message

Recent commits:
$(git log --oneline -5)

Files to be updated:
- VERSION
- CHANGELOG.md  
- wp-content/themes/meup-child/style.css

Actions:
- ✅ Create git commit
- ✅ Create git tag v$new_version
- ✅ Push to GitHub origin/main
- ✅ Trigger automated deployment

=====================================

EOF
}

# Confirm release
confirm_release() {
    local summary="$1"
    
    echo "$summary"
    
    echo -e "${YELLOW}⚠️  This will create a new release and deploy to production${NC}"
    echo -e "${YELLOW}⚠️  Make sure you have reviewed all changes carefully${NC}"
    echo ""
    
    read -p "Proceed with release? (y/N): " -r
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        log_info "Release cancelled by user"
        return 1
    fi
    
    return 0
}

# Execute release workflow
execute_release() {
    local release_type="$1"
    local release_message="$2"
    local create_tag="$3"
    local push_changes="$4"
    local update_changelog="$5"
    
    log_step "Executing release workflow..."
    
    # Generate changelog from commits
    if [[ "$update_changelog" == "true" ]]; then
        log_info "Generating changelog from commits..."
        "$SCRIPT_DIR/update-changelog.sh" --dry-run | head -20
        echo ""
        read -p "Update changelog with these entries? (y/N): " -r
        if [[ $REPLY =~ ^[Yy]$ ]]; then
            "$SCRIPT_DIR/update-changelog.sh"
            log_success "Changelog updated"
        fi
    fi
    
    # Build bump-version command
    local bump_cmd="$SCRIPT_DIR/bump-version.sh $release_type"
    
    if [[ -n "$release_message" ]]; then
        bump_cmd="$bump_cmd --message '$release_message'"
    fi
    
    if [[ "$create_tag" == "true" ]]; then
        bump_cmd="$bump_cmd --tag"
    fi
    
    if [[ "$push_changes" == "true" ]]; then
        bump_cmd="$bump_cmd --push"
    fi
    
    # Execute version bump
    log_info "Executing: $bump_cmd"
    eval "$bump_cmd"
    
    log_success "Release workflow completed!"
}

# Verify deployment
verify_deployment() {
    local new_version="$1"
    
    log_step "Verifying deployment..."
    
    # Wait a moment for webhook to trigger
    log_info "Waiting for webhook deployment (30s)..."
    sleep 30
    
    # Check if we can reach the site
    local site_url="https://afroticket.ca"
    if command -v curl > /dev/null; then
        log_info "Testing site accessibility..."
        if curl -s --head "$site_url" | grep -q "200 OK"; then
            log_success "Site is accessible: $site_url"
        else
            log_warning "Site accessibility test failed"
        fi
    fi
    
    log_info "Deployment verification completed"
    log_info "Monitor webhook logs at: /home/u493216327/webhook-deploy.log"
}

# Main function
main() {
    local release_type=""
    local release_message=""
    local dry_run=false
    local skip_tests=false
    local force_mode=false
    local create_tag=true
    local push_changes=true
    local update_changelog=true
    
    # Parse command line arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            patch|minor|major)
                release_type=$1
                shift
                ;;
            -m|--message)
                release_message=$2
                shift 2
                ;;
            -d|--dry-run)
                dry_run=true
                shift
                ;;
            -s|--skip-tests)
                skip_tests=true
                shift
                ;;
            -f|--force)
                force_mode=true
                shift
                ;;
            --no-push)
                push_changes=false
                shift
                ;;
            --no-tag)
                create_tag=false
                shift
                ;;
            --no-changelog)
                update_changelog=false
                shift
                ;;
            -h|--help)
                show_usage
                exit 0
                ;;
            *)
                log_error "Unknown option: $1"
                show_usage
                exit 1
                ;;
        esac
    done
    
    # Validate required arguments
    if [[ -z "$release_type" ]]; then
        log_error "Release type required (patch|minor|major)"
        show_usage
        exit 1
    fi
    
    # Set default release message if not provided
    if [[ -z "$release_message" ]]; then
        case $release_type in
            patch)
                release_message="Bug fixes and improvements"
                ;;
            minor)
                release_message="New features and enhancements"
                ;;
            major)
                release_message="Major release with breaking changes"
                ;;
        esac
    fi
    
    echo ""
    log_info "🚀 AfroTicket WordPress Release Manager"
    echo ""
    
    # Validate environment
    validate_environment || exit 1
    
    # Check working tree
    check_working_tree "$force_mode" || exit 1
    
    # Run tests
    if [[ "$skip_tests" != "true" ]]; then
        run_tests || exit 1
    fi
    
    # Get current version for summary
    local current_version=$(get_current_version)
    local new_version=""
    
    # Calculate new version for summary
    case $release_type in
        patch)
            new_version=$(echo "$current_version" | awk -F. '{print $1"."$2"."($3+1)}')
            ;;
        minor)
            new_version=$(echo "$current_version" | awk -F. '{print $1"."($2+1)".0"}')
            ;;
        major)
            new_version=$(echo "$current_version" | awk -F. '{print ($1+1)".0.0"}')
            ;;
    esac
    
    # Generate and show release summary
    local summary=$(generate_release_summary "$current_version" "$new_version" "$release_message")
    
    if [[ "$dry_run" == "true" ]]; then
        log_info "DRY RUN MODE - No changes will be made"
        echo "$summary"
        exit 0
    fi
    
    # Confirm release
    confirm_release "$summary" || exit 0
    
    # Execute release
    execute_release "$release_type" "$release_message" "$create_tag" "$push_changes" "$update_changelog"
    
    # Verify deployment if pushed
    if [[ "$push_changes" == "true" ]]; then
        verify_deployment "$new_version"
    fi
    
    echo ""
    log_success "🎉 Release $new_version completed successfully!"
    log_info "🌐 Live site: https://afroticket.ca"
    log_info "📋 GitHub: https://github.com/dimitryKamga/afroticket-wordpress"
    
    if [[ "$create_tag" == "true" ]]; then
        log_info "🏷️  Tag created: v$new_version"
    fi
    
    echo ""
}

# Run main function
main "$@"