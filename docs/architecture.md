# Architecture

```
Business TX
  ├─ write aggregates
  └─ Outbox::record(...) → outbox_messages (pending)
         │
outbox:publish (claim batch)
         ├─ map type → Laravel event (optional)
         ├─ fire OutboxMessagePublished
         └─ mark published | retry pending | failed
```

## Guarantees

- **At-least-once** publication after commit (worker may retry).
- **Not** exactly-once across external systems—use `IdempotentConsumer` downstream.
- `require_transaction=true` (default) prevents accidental dual-write outside a TX.

## SQLite note

`lockForUpdate` is skipped on SQLite; fine for tests/single-process. Use MySQL/Postgres in production.
