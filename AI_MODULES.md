# AI Across Modules

Every module can call `POST /api/v1/ai/{module}/analyze` with capability `assistant`, `forecast`, `anomaly_detection`, `recommendation`, `ocr`, or `scoring`.

The engine currently runs safely in local baseline mode when no provider is configured. Set the provider and credentials through the existing AI settings before enabling external LLM calls.
