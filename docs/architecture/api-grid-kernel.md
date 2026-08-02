# API Grid transport and mapping kernel

`zoosper-api-grid` adapts external read-only collection APIs to the transport-neutral contracts in `zoosper-grid`.

The package owns request and response value objects, a replaceable transport interface, read-only reliability policy, authentication strategy, trusted Grid context and request/response/row mapper contracts. It contains no concrete HTTP library, endpoint URL, credentials, Orders schema, HTML or admin controller.

`ApiGridDataSource` performs one pipeline: map the neutral query, apply authentication, send through the injected transport, reject non-success responses, and map the successful response into `GridResult`. Tests use fake transports only.
