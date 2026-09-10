import test from 'node:test';
import assert from 'node:assert/strict';
import { checkout } from '../resources/js/checkout.js';

const account = { name: 'Customer', email: 'customer@example.com', phone: '01012345678' };
const config = { user: null, gameId: 1, games: [{ id: 1, price: '50.00' }, { id: 2, price: '75.00' }] };

test('account creation rotates CSRF before the receipt request and clears the password', async () => {
    const token = { content: 'old-token' }, hiddenToken = { value: 'old-token' }, password = { value: 'secret-password' };
    const original = { document: globalThis.document, window: globalThis.window, FormData: globalThis.FormData, fetch: globalThis.fetch, CustomEvent: globalThis.CustomEvent };
    try {
        globalThis.document = { querySelector: () => token, querySelectorAll: () => [hiddenToken] };
        globalThis.window = { dispatchEvent() {}, matchMedia: () => ({ matches: true }) };
        globalThis.CustomEvent = class { constructor(type, options) { this.type = type; this.detail = options.detail; } };
        globalThis.FormData = class { constructor(form) { this.fields = form.fields; } };
        const requests = [];
        globalThis.fetch = async (url, options) => {
            requests.push({ url, options });
            return { ok: true, status: 200, json: async () => ({ user: account, csrf_token: 'new-token', message: 'received' }) };
        };
        const state = checkout(config);
        state.$nextTick = callback => callback();
        state.$refs = { payment: { scrollIntoView() {} }, success: { focus() {}, scrollIntoView() {} } };
        await state.authenticate({ action: '/checkout/register', fields: account, querySelectorAll: () => [password] });
        assert.deepEqual(state.details, { full_name: account.name, email: account.email, phone: account.phone });
        assert.equal(token.content, 'new-token');
        assert.equal(hiddenToken.value, 'new-token');
        assert.equal(password.value, '');
        await state.submitPayment({ action: '/subscribe', fields: { receipt_image: 'receipt' }, reset() {} });
        assert.equal(requests[0].options.headers['X-CSRF-TOKEN'], 'old-token');
        assert.equal(requests[1].options.headers['X-CSRF-TOKEN'], 'new-token');
        assert.equal(state.success, true);
    } finally { Object.assign(globalThis, original); }
});

test('switching game updates amount without losing customer data', () => {
    const state = checkout({ ...config, user: account });
    state.selectedGame = '2';
    assert.equal(state.amount, '75.00');
    assert.equal(state.details.phone, account.phone);
});

test('failed receipt submission preserves the selected file and allows retry', async () => {
    const originalFormData = globalThis.FormData;
    globalThis.FormData = class {};
    try {
        const state = checkout({ ...config, user: account });
        state.preview = 'blob:receipt';
        state.request = async () => { throw { messages: ['تعذر الاتصال'] }; };
        let reset = false;
        await state.submitPayment({ action: '/subscribe', reset() { reset = true; } });
        assert.equal(state.preview, 'blob:receipt');
        assert.equal(reset, false);
        assert.equal(state.paymentBusy, false);
        assert.equal(state.success, false);
        assert.deepEqual(state.paymentErrors, ['تعذر الاتصال']);
    } finally { globalThis.FormData = originalFormData; }
});

test('guest and duplicate submissions do not send another receipt', async () => {
    const state = checkout(config);
    let requests = 0;
    state.request = async () => { requests++; };
    await state.submitPayment({});
    assert.equal(requests, 0);
    state.user = account;
    state.paymentBusy = true;
    await state.submitPayment({});
    assert.equal(requests, 0);
});
