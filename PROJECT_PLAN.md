# Event Outbox — Project Plan

## Name

**event-outbox** (`hekal/event-outbox`)

## Vision

Transactional outbox for Laravel: write domain events in the same DB transaction as business state, then publish reliably via a worker—eliminating dual-write loss between DB and queue/events.

## v0.1

- `Outbox::record($type, $payload, $headers?)` inside a transaction
- `outbox_messages` storage with pending → published lifecycle
- `outbox:publish` command (batch claim + dispatch)
- Laravel event bridge (`OutboxMessagePublished` + optional type→event map)
- Processed-message table helper for idempotent consumers
- Purge published messages command

## Out

- Multi-broker transports (Kafka/Rabbit) — document as future
- Exactly-once across systems (not claimed)
