# Change Log

## [Unreleased]
### Added
- Implemented re-fetch API to re-fetch all previous month worth of
  chunks the 6. every month.

### Fixed
- Increased size of most database columns. char(n) → char(), integer →
  bigInteger, float → double
- Allow null values on STLTicketVersionNo and Origin

### Removed
- Removed lots of unused db columns.

## [0.1.0] – 2024-04-18

### Added
- Implemented sink API for stage 1: Raw retrieval (fetch)
- Implemented sink API for stage 2: DB import and removal
- Implemented sink API for schemas and their descriptions
- Implemented sink API for reverse chunk ID lookup
