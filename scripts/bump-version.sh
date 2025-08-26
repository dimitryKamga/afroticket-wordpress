#!/bin/bash

# AfroTicket WordPress Version Bumper
# Automatically increment semantic version based on change type

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
VERSION_FILE="VERSION"
THEME_STYLE_FILE="wp-content/themes/meup-child/style.css"
CHANGELOG_FILE="CHANGELOG.md"

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

# Show usage information
show_usage() {
    cat << EOF
Usage: $0 [patch|minor|major] [OPTIONS]

Bump version number following semantic versioning (MAJOR.MINOR.PATCH)

Arguments:
  patch    Increment patch version (bug fixes)
  minor    Increment minor version (new features) 
  major    Increment major version (breaking changes)

Options:
  -m, --message MESSAGE    Custom commit message
  -t, --tag               Create git tag after version bump
  -p, --push              Push changes to remote repository
  -c, --changelog         Update changelog with new version
  -h, --help              Show this help message

Examples:
  $0 patch                           # 1.1.0 → 1.1.1
  $0 minor -m "Add SMS system"       # 1.1.0 → 1.2.0  
  $0 major -t -p                     # 1.1.0 → 2.0.0 (tag & push)

EOF
}

# Validate version file exists
check_version_file() {
    if [[ ! -f "$VERSION_FILE" ]]; then
        log_error "VERSION file not found. Creating with version 1.0.0"
        echo "1.0.0" > "$VERSION_FILE"
    fi
}

# Parse current version
get_current_version() {
    if [[ -f "$VERSION_FILE" ]]; then
        cat "$VERSION_FILE"
    else
        echo "1.0.0"
    fi
}

# Parse version components
parse_version() {
    local version=$1
    if [[ $version =~ ^([0-9]+)\.([0-9]+)\.([0-9]+)$ ]]; then
        MAJOR=${BASH_REMATCH[1]}
        MINOR=${BASH_REMATCH[2]}
        PATCH=${BASH_REMATCH[3]}
        return 0
    else
        log_error "Invalid version format: $version"
        return 1
    fi
}

# Calculate new version
calculate_new_version() {
    local bump_type=$1
    local current_version=$2
    
    parse_version "$current_version" || return 1
    
    case $bump_type in
        patch)
            NEW_VERSION="$MAJOR.$MINOR.$((PATCH + 1))"
            ;;
        minor)
            NEW_VERSION="$MAJOR.$((MINOR + 1)).0"
            ;;
        major)
            NEW_VERSION="$((MAJOR + 1)).0.0"
            ;;
        *)
            log_error "Invalid bump type: $bump_type"
            return 1
            ;;
    esac
    
    echo "$NEW_VERSION"
}

# Update version in files
update_version_files() {
    local new_version=$1
    
    # Update VERSION file
    echo "$new_version" > "$VERSION_FILE"
    log_success "Updated VERSION file: $new_version"
    
    # Update WordPress theme style.css
    if [[ -f "$THEME_STYLE_FILE" ]]; then
        sed -i.bak "s/^Version: .*/Version: $new_version/" "$THEME_STYLE_FILE"
        rm "${THEME_STYLE_FILE}.bak" 2>/dev/null || true
        log_success "Updated theme version: $THEME_STYLE_FILE"
    else
        log_warning "Theme style file not found: $THEME_STYLE_FILE"
    fi
}

# Update changelog with new version entry
update_changelog() {
    local new_version=$1
    local current_date=$(date '+%Y-%m-%d')
    
    if [[ -f "$CHANGELOG_FILE" ]]; then
        # Create temporary file with new version entry
        local temp_file=$(mktemp)
        
        # Find the line containing "# Changelog" and insert after it
        awk -v version="$new_version" -v date="$current_date" '
        /^# Changelog/ {
            print $0
            print ""
            print "All notable changes to the AfroTicket WordPress project will be documented in this file."
            print ""
            print "The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),"
            print "and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html)."
            print ""
            print "## [" version "] - " date
            print ""
            print "### Added"
            print "- "
            print ""
            print "### Changed" 
            print "- "
            print ""
            print "### Fixed"
            print "- "
            print ""
            # Skip the existing header lines
            for(i=0; i<5; i++) getline
            next
        }
        { print }
        ' "$CHANGELOG_FILE" > "$temp_file"
        
        mv "$temp_file" "$CHANGELOG_FILE"
        log_success "Added new version entry to changelog"
    else
        log_warning "Changelog file not found: $CHANGELOG_FILE"
    fi
}

# Create git commit
create_git_commit() {
    local new_version=$1
    local custom_message=$2
    
    # Stage version-related files
    git add "$VERSION_FILE" 2>/dev/null || true
    git add "$THEME_STYLE_FILE" 2>/dev/null || true
    git add "$CHANGELOG_FILE" 2>/dev/null || true
    
    # Create commit message
    local commit_message
    if [[ -n "$custom_message" ]]; then
        commit_message="chore: bump version to $new_version - $custom_message"
    else
        commit_message="chore: bump version to $new_version"
    fi
    
    # Create commit
    git commit -m "$commit_message

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>"
    
    log_success "Created git commit: $commit_message"
}

# Create git tag
create_git_tag() {
    local new_version=$1
    
    git tag -a "v$new_version" -m "Release version $new_version

🤖 Generated with [Claude Code](https://claude.ai/code)"
    
    log_success "Created git tag: v$new_version"
}

# Push changes to remote
push_to_remote() {
    git push origin main
    git push origin --tags
    log_success "Pushed changes and tags to remote repository"
}

# Main function
main() {
    local bump_type=""
    local custom_message=""
    local create_tag=false
    local push_changes=false
    local update_changelog_flag=false
    
    # Parse command line arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            patch|minor|major)
                bump_type=$1
                shift
                ;;
            -m|--message)
                custom_message=$2
                shift 2
                ;;
            -t|--tag)
                create_tag=true
                shift
                ;;
            -p|--push)
                push_changes=true
                shift
                ;;
            -c|--changelog)
                update_changelog_flag=true
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
    
    # Validate bump type
    if [[ -z "$bump_type" ]]; then
        log_error "Version bump type required (patch|minor|major)"
        show_usage
        exit 1
    fi
    
    # Check if we're in a git repository
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        log_error "Not in a git repository"
        exit 1
    fi
    
    # Check for uncommitted changes
    if [[ -n $(git status --porcelain) ]]; then
        log_warning "You have uncommitted changes. Commit or stash them first."
        git status --short
        exit 1
    fi
    
    log_info "Starting version bump process..."
    
    # Get current version
    check_version_file
    local current_version=$(get_current_version)
    log_info "Current version: $current_version"
    
    # Calculate new version
    local new_version=$(calculate_new_version "$bump_type" "$current_version")
    if [[ $? -ne 0 ]]; then
        exit 1
    fi
    
    log_info "New version: $new_version"
    
    # Confirm version bump
    echo
    log_warning "This will bump version from $current_version to $new_version"
    read -p "Continue? (y/N): " -r
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        log_info "Version bump cancelled"
        exit 0
    fi
    
    # Update version files
    update_version_files "$new_version"
    
    # Update changelog if requested
    if [[ "$update_changelog_flag" == true ]]; then
        update_changelog "$new_version"
    fi
    
    # Create git commit
    create_git_commit "$new_version" "$custom_message"
    
    # Create git tag if requested
    if [[ "$create_tag" == true ]]; then
        create_git_tag "$new_version"
    fi
    
    # Push changes if requested
    if [[ "$push_changes" == true ]]; then
        push_to_remote
    fi
    
    echo
    log_success "Version bump completed successfully!"
    log_info "Version: $current_version → $new_version"
    
    if [[ "$create_tag" == false ]]; then
        log_info "Run with -t flag to create git tag"
    fi
    
    if [[ "$push_changes" == false ]]; then
        log_info "Run with -p flag to push changes to remote"
    fi
}

# Run main function
main "$@"