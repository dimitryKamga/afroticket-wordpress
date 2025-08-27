#!/bin/bash

# AfroTicket WordPress Changelog Generator
# Automatically generate changelog entries from Git commits

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
CHANGELOG_FILE="CHANGELOG.md"
VERSION_FILE="VERSION"

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
Usage: $0 [OPTIONS]

Generate changelog entries from Git commits using conventional commit format

Options:
  -v, --version VERSION       Target version for changelog entry
  -f, --from-tag TAG         Generate changelog from specific tag
  -t, --to-tag TAG           Generate changelog to specific tag (default: HEAD)
  -d, --dry-run              Show what would be generated without updating files
  -a, --append               Append to existing changelog (default: prepend)
  -h, --help                 Show this help message

Conventional Commit Types:
  feat:     New feature (minor version)
  fix:      Bug fix (patch version) 
  docs:     Documentation changes
  style:    Code style changes
  refactor: Code refactoring
  test:     Adding tests
  chore:    Build process or auxiliary tool changes

Examples:
  $0                                    # Generate from last tag to HEAD
  $0 -v 1.2.0                         # Generate for specific version
  $0 -f v1.0.0 -t v1.1.0              # Generate between specific tags
  $0 --dry-run                         # Preview changes without modifying files

EOF
}

# Get current version from VERSION file
get_current_version() {
    if [[ -f "$VERSION_FILE" ]]; then
        cat "$VERSION_FILE"
    else
        echo "1.0.0"
    fi
}

# Get the last git tag
get_last_tag() {
    local last_tag=$(git describe --tags --abbrev=0 2>/dev/null || echo "")
    if [[ -z "$last_tag" ]]; then
        # If no tags, use first commit
        git rev-list --max-parents=0 HEAD 2>/dev/null || echo "HEAD"
    else
        echo "$last_tag"
    fi
}

# Parse commit message and categorize
categorize_commit() {
    local commit_msg="$1"
    local commit_hash="$2"
    local short_hash="${commit_hash:0:7}"
    
    # Remove conventional commit prefixes and extract description
    local clean_msg=""
    local category=""
    
    case "$commit_msg" in
        feat:*|feature:*)
            category="Added"
            clean_msg=$(echo "$commit_msg" | sed 's/^feat[ure]*: *//')
            ;;
        fix:*)
            category="Fixed"
            clean_msg=$(echo "$commit_msg" | sed 's/^fix: *//')
            ;;
        docs:*)
            category="Documentation"
            clean_msg=$(echo "$commit_msg" | sed 's/^docs: *//')
            ;;
        style:*)
            category="Changed"
            clean_msg=$(echo "$commit_msg" | sed 's/^style: *//')
            ;;
        refactor:*)
            category="Changed"
            clean_msg=$(echo "$commit_msg" | sed 's/^refactor: *//')
            ;;
        test:*)
            category="Testing"
            clean_msg=$(echo "$commit_msg" | sed 's/^test: *//')
            ;;
        chore:*)
            category="Maintenance"
            clean_msg=$(echo "$commit_msg" | sed 's/^chore: *//')
            ;;
        *)
            # Non-conventional commits
            if [[ "$commit_msg" =~ ^(Add|Added) ]]; then
                category="Added"
            elif [[ "$commit_msg" =~ ^(Fix|Fixed) ]]; then
                category="Fixed"
            elif [[ "$commit_msg" =~ ^(Update|Updated|Change|Changed) ]]; then
                category="Changed"
            elif [[ "$commit_msg" =~ ^(Remove|Removed) ]]; then
                category="Removed"
            else
                category="Changed"
            fi
            clean_msg="$commit_msg"
            ;;
    esac
    
    # Capitalize first letter if not already
    clean_msg="$(echo "${clean_msg:0:1}" | tr '[:lower:]' '[:upper:]')${clean_msg:1}"
    
    # Return as category|message|hash
    echo "${category}|${clean_msg}|${short_hash}"
}

# Generate changelog section
generate_changelog_section() {
    local from_ref="$1"
    local to_ref="$2"
    local version="$3"
    local current_date=$(date '+%Y-%m-%d')
    
    log_info "Analyzing commits from $from_ref to $to_ref"
    
    # Get commit messages and hashes
    local commits=$(git log --pretty=format:"%H|%s" "$from_ref..$to_ref" 2>/dev/null || \
                   git log --pretty=format:"%H|%s" "$to_ref" 2>/dev/null)
    
    if [[ -z "$commits" ]]; then
        log_warning "No commits found in range $from_ref..$to_ref"
        return 1
    fi
    
    # Initialize category arrays (bash 3.x compatible)
    local added=""
    local changed=""
    local fixed=""
    local removed=""
    local security=""
    local documentation=""
    local testing=""
    local maintenance=""
    
    # Process each commit
    while IFS='|' read -r hash subject; do
        # Skip empty lines
        [[ -z "$hash" ]] && continue
        
        # Skip merge commits and automated commits
        if [[ "$subject" =~ ^Merge.* ]] || [[ "$subject" =~ "Generated with \[Claude Code\]" ]]; then
            continue
        fi
        
        # Categorize commit
        local result=$(categorize_commit "$subject" "$hash")
        IFS='|' read -r category message short_hash <<< "$result"
        
        # Add to appropriate category
        case "$category" in
            "Added")
                if [[ -n "$added" ]]; then added="$added\n"; fi
                added="$added- $message (\`$short_hash\`)"
                ;;
            "Changed")
                if [[ -n "$changed" ]]; then changed="$changed\n"; fi
                changed="$changed- $message (\`$short_hash\`)"
                ;;
            "Fixed")
                if [[ -n "$fixed" ]]; then fixed="$fixed\n"; fi
                fixed="$fixed- $message (\`$short_hash\`)"
                ;;
            "Removed")
                if [[ -n "$removed" ]]; then removed="$removed\n"; fi
                removed="$removed- $message (\`$short_hash\`)"
                ;;
            "Security")
                if [[ -n "$security" ]]; then security="$security\n"; fi
                security="$security- $message (\`$short_hash\`)"
                ;;
            "Documentation")
                if [[ -n "$documentation" ]]; then documentation="$documentation\n"; fi
                documentation="$documentation- $message (\`$short_hash\`)"
                ;;
            "Testing")
                if [[ -n "$testing" ]]; then testing="$testing\n"; fi
                testing="$testing- $message (\`$short_hash\`)"
                ;;
            "Maintenance")
                if [[ -n "$maintenance" ]]; then maintenance="$maintenance\n"; fi
                maintenance="$maintenance- $message (\`$short_hash\`)"
                ;;
        esac
    done <<< "$commits"
    
    # Generate changelog entry
    echo "## [$version] - $current_date"
    echo ""
    
    # Output categories that have content
    if [[ -n "$added" ]]; then
        echo "### Added"
        echo -e "$added"
        echo ""
    fi
    
    if [[ -n "$changed" ]]; then
        echo "### Changed"
        echo -e "$changed"
        echo ""
    fi
    
    if [[ -n "$fixed" ]]; then
        echo "### Fixed"
        echo -e "$fixed"
        echo ""
    fi
    
    if [[ -n "$removed" ]]; then
        echo "### Removed"
        echo -e "$removed"
        echo ""
    fi
    
    if [[ -n "$security" ]]; then
        echo "### Security"
        echo -e "$security"
        echo ""
    fi
    
    if [[ -n "$documentation" ]]; then
        echo "### Documentation"
        echo -e "$documentation"
        echo ""
    fi
    
    if [[ -n "$testing" ]]; then
        echo "### Testing"
        echo -e "$testing"
        echo ""
    fi
    
    if [[ -n "$maintenance" ]]; then
        echo "### Maintenance"
        echo -e "$maintenance"
        echo ""
    fi
}

# Update changelog file
update_changelog_file() {
    local new_section="$1"
    local append_mode="$2"
    
    if [[ ! -f "$CHANGELOG_FILE" ]]; then
        log_error "Changelog file not found: $CHANGELOG_FILE"
        return 1
    fi
    
    local temp_file=$(mktemp)
    
    if [[ "$append_mode" == "true" ]]; then
        # Append to end of file
        cat "$CHANGELOG_FILE" > "$temp_file"
        echo "" >> "$temp_file"
        echo "$new_section" >> "$temp_file"
    else
        # Prepend after header
        local header_found=false
        while IFS= read -r line; do
            echo "$line" >> "$temp_file"
            
            # Insert after the header section
            if [[ "$line" =~ ^##.* ]] && [[ "$header_found" == false ]]; then
                echo "" >> "$temp_file"
                echo "$new_section" >> "$temp_file"
                echo "" >> "$temp_file"
                header_found=true
            fi
        done < "$CHANGELOG_FILE"
        
        # If no existing version sections, append to end
        if [[ "$header_found" == false ]]; then
            echo "" >> "$temp_file"
            echo "$new_section" >> "$temp_file"
        fi
    fi
    
    mv "$temp_file" "$CHANGELOG_FILE"
}

# Main function
main() {
    local target_version=""
    local from_tag=""
    local to_tag="HEAD"
    local dry_run=false
    local append_mode=false
    
    # Parse command line arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            -v|--version)
                target_version="$2"
                shift 2
                ;;
            -f|--from-tag)
                from_tag="$2"
                shift 2
                ;;
            -t|--to-tag)
                to_tag="$2"
                shift 2
                ;;
            -d|--dry-run)
                dry_run=true
                shift
                ;;
            -a|--append)
                append_mode=true
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
    
    # Check if we're in a git repository
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        log_error "Not in a git repository"
        exit 1
    fi
    
    # Set default from_tag if not specified
    if [[ -z "$from_tag" ]]; then
        from_tag=$(get_last_tag)
    fi
    
    # Set default target_version if not specified
    if [[ -z "$target_version" ]]; then
        target_version=$(get_current_version)
    fi
    
    log_info "Generating changelog for version $target_version"
    log_info "Commit range: $from_tag..$to_tag"
    
    # Generate changelog section
    local changelog_section=$(generate_changelog_section "$from_tag" "$to_tag" "$target_version")
    
    if [[ -z "$changelog_section" ]]; then
        log_error "Failed to generate changelog section"
        exit 1
    fi
    
    if [[ "$dry_run" == true ]]; then
        log_info "Dry run mode - showing generated changelog:"
        echo ""
        echo "=================="
        echo "$changelog_section"
        echo "=================="
        echo ""
        log_info "Use without --dry-run to update $CHANGELOG_FILE"
    else
        # Update changelog file
        update_changelog_file "$changelog_section" "$append_mode"
        log_success "Updated changelog: $CHANGELOG_FILE"
        
        # Show summary
        echo ""
        log_info "Generated changelog entry for version $target_version"
        echo "$(echo "$changelog_section" | head -20)"
        if [[ $(echo "$changelog_section" | wc -l) -gt 20 ]]; then
            echo "... (truncated)"
        fi
    fi
}

# Run main function
main "$@"