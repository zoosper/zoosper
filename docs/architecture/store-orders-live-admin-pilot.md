# Store Orders live admin pilot

The pilot uses the existing module-owned admin route, controller factory, ACL, menu and layout conventions. A bounded cURL JSON transport is injected behind `ApiTransportInterface`; the API base URL and trusted store scope come from server configuration only.

Required environment values:

- `STORE_ORDERS_API_BASE_URL`, defaulting to `http://127.0.0.1:3000`;
- `STORE_ORDERS_STORE_CODE`, with no usable default;
- `STORE_ORDERS_KIOSK_WEBSITE_ID`, with no usable default.

Optional timeout and response-size settings are available. Redirect following is disabled, only the read-only request contract is accepted, JSON decoding is strict, and failures render a 503 service-unavailable state rather than an empty Grid.
