# API Grid reliability hardening

Phase 7C gives transport failures stable categories, classifies timeouts separately, rejects non-success status codes before decoding untrusted error bodies, bounds response accumulation while cURL is receiving data, and removes low-level cURL diagnostics from administrator-facing exception messages.

The generic data source retains a defensive non-success check because `ApiTransportInterface` is replaceable and fake or third-party transports may return `ApiResponse` directly without using `CurlJsonApiTransport`. This preserves the established contract that remote failures are never converted into valid empty Grid results.

The transport never includes endpoint URLs, headers, response bodies or cURL error strings in public messages. Retries remain disabled by default. Cursor pagination and a real second pilot remain separate phases.
