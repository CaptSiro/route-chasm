# jsml: building DOM in RouteChasm

`components/Jsml.js` is the front-end view library. It builds real DOM nodes without a build step. It has three
equivalent syntaxes, components, fine-grained reactivity (SolidJS style) and full `Impulse` support. It replaces the
original `jsml` and is backwards compatible with it (see [Differences from the original jsml](#differences-from-the-original-jsml)).

```js
jsml.div("card", [jsml.h3(_, title), jsml.p(_, () => count())])     // proxy API (the original one, extended)
jsml(Card, { title }, jsml.p(_, "body"))                           // h()-style, like React.createElement
html`<${Card} title=${title}><p>body</p></>`                       // tagged template, closest to JSX
```

Read the first four sections before writing or reviewing jsml code. They cover the rules that keep code safe
and correct. The API reference comes after them.

---

## Rules of thumb (the short version)

1. **Pass the accessor, not its value.** `count` and `() => count() * 2` are live, while `count()` reads the value once and never updates.
2. **Components run once.** A component body is not re-run on changes. Only functions and Impulses placed into content or attributes update.
3. **Strings are always text.** Content and `${…}` values are never parsed as HTML. Keep it that way: don't reach for `innerHTML` or `Html()` with data.
4. **Never spread untrusted objects as props, and never use untrusted strings as tag names or URLs.**
5. **Event handlers must be functions.** `onClick: "…"` throws on purpose.
6. **Reactive UI built outside `render()` is never cleaned up.** If you mount and unmount UI repeatedly, use `render()` and call the returned disposer.
7. **Errors inside reactive bindings and effects are logged with `console.error`, not thrown.** Check the console.
8. **Updates are synchronous.** Wrap several writes in `batch()`.
9. **`<For>` is keyed by object identity.** Recreating the item objects on every refresh rebuilds every row.

---

## Security

| Do not | Why | Instead |
|---|---|---|
| `el.innerHTML = data`, `Html(data)` (Components.js) | Parses HTML, which means XSS | Pass the string as content: `jsml.p(_, data)` or `html\`<p>${data}</p>\`` |
| `html([userString])`, building template strings dynamically | The static parts of a template are parsed as trusted markup | Always use `html` as a tag on a literal: ``html`…${value}…` `` |
| `jsml.a({ href: userUrl })`, `src`, `action`, `formaction`, `xlink:href` | Attribute values are not sanitized, so `javascript:` URLs run code | Validate the scheme (`http:`, `https:`, relative) before binding |
| `jsml.div(userObject)`, `<div ...${userObject}>` | Keys become attributes. `\onclick`, `attr:onclick` and `prop:innerHTML` are deliberate escape hatches and run code | Build props from known keys only |
| `jsml(userTag)`, `<${userTag}>`, `jsml[userTag]()` | Can create `<script>`, `<iframe>`, `<object>`, … | Use a whitelist of tag names |
| `onClick: "doSomething()"` | Inline handlers are code strings | Pass a function. A string throws a `TypeError`, and `null`/`undefined` are skipped |

What is safe by construction:
- Every string, number and Impulse value in content becomes a `Text` node.
- Every `${value}` in a template is a value, never markup. That includes values inside attributes (`title="Hi ${name}"`).
- Only the escape hatches above set inline handlers: a `\on…` key, `attr:on…` and `prop:…`. They are intended for developers and must never come from data.

---

## Gotchas (behavior)

### Reactivity

- **`n` vs `n()`.** `jsml.p(_, n)` and `jsml.p(_, () => n() + 1)` stay live. `jsml.p(_, n())` and `` `${n()}` `` are snapshots.
  In templates: `${n}` is live and `${n()}` is static.
- **A component body is not a reactive scope.** `const label = props.done() ? "x" : "y"` inside a component is computed once.
  Write `() => props.done() ? "x" : "y"` instead, or use `memo()`.
- **Props are plain values, not getters.** A reactive prop is an accessor or an Impulse that the parent passed in. Inside a
  component, use `read(props.x)` within a binding (`() => read(props.x)`). Destructuring props is fine, because the accessors
  are themselves the reactive part.
- **Any function in content or in a non-`on*` element attribute is a live binding.** It gets called with no arguments. So
  `jsml.input({ validate: checkFn })` sets the attribute `validate` to `checkFn()`. Components receive functions as plain props.
- **To store a function in a signal**, use `setFn(() => myFunction)`. The setter treats a function argument as an updater.
- **Signals compare with `===`** by default, so writing the same value does nothing. Mutating an object or array in
  place does not notify anyone: write a new object, or create the signal with `signal(x, { equals: false })`.
- **Reactive `class` owns the whole `class` attribute.** When the class is an array, object map or accessor, every update
  rewrites the attribute, which wipes `classList.add()` changes made by hand. A plain string `class` is set once.
  A reactive `style` object only touches its own keys.
- **Anything with `listen()` and `value()` methods is treated as an Impulse** (duck typing).
- **Errors thrown in an effect, memo or binding are caught and logged** (`console.error`), so the rest of the UI keeps working.
  They do not reach the caller of the setter.
- An update loop (a computation that keeps writing to its own dependency) is stopped after 100 000 iterations, and the error is logged.

### Components and children

- **In `html` templates, `props.children` is lazy.** Each access creates new nodes, so reading it twice renders two copies.
  Read it once, or use `const kids = children(() => props.children)`.
- **In the function API, children are created before the component runs.** For context to reach them, pass them lazily:
  `jsml(Theme.Provider, { value }, () => jsml(Child))`. Templates handle this automatically.
- `jsml.Name(...)` with a capital letter throws a `ReferenceError` until it is registered with `jsml.define({ Name })`.
  The registry is global, and the last registration wins. `jsml.define`, `jsml.Show`, `jsml.For` and `jsml.Fragment` are reserved.
- `onCleanup()` outside a component or `render()` scope is silently ignored. `onMount()` outside `render()` runs in a microtask.

### Elements, attributes, events

- **Proxy API attribute names are converted from camelCase to kebab-case**: `dataTestid` becomes `data-testid` and
  `ariaLabel` becomes `aria-label`, so write `tabindex`, not `tabIndex`. SVG camelCase attributes (`viewBox`, `stdDeviation`, …)
  are kept as they are. **Templates use names exactly as written.**
- **Event names are lower-cased**: `onClick` listens to `click` and `onKeyDown` to `keydown`. For case-sensitive custom
  events use `on:exactName`.
- `value`, `checked`, `selected`, `muted` and `indeterminate` are also written as DOM properties, so bindings keep working
  after the user has edited the field.
- `null`, `undefined`, `false` and `true` render nothing in content. On attributes, `false`, `null` and `undefined` remove the
  attribute, and `true` sets it empty (`disabled=""`).
- The proxy API creates SVG elements only for the SVG tag names it knows (`svg`, `path`, `circle`, `g`, `use`, `text`, …,
  listed in `JSML_SVG_TAGS`). Inside templates, everything under `<svg>` is in the SVG namespace.

### Templates

- `html` returns a single node when the template has exactly one root, and an array otherwise. Text pieces and `${…}` values
  next to each other count as separate roots, so ``html`hello ${name}` `` is an array. Its type is therefore `JsmlContent`,
  which DOM methods don't accept. To pass a template to `append`, `replaceWith`, `insertBefore` and similar methods, use
  `html.node` (always a `Node`). Use `html.element` when you need the element itself.
- Whitespace follows JSX rules: lines are trimmed, and whitespace that contains a newline between tags is dropped.
  Write `${" "}` to keep a space.
- Close components with `</>`, `<//>` or `</${Comp}>`. Fragments are `<>…</>`. Plain tags must be closed by name (`</div>`).
  Void tags (`<input>`, `<br>`, …) need no closing tag. `<x />` self-closes anything.
- Templates are not type-checked by editors: `${…}` values are `any`.

### Loading

- `Jsml.js` (followed by `JsmlSideLoader.js`) is included in `<head>` with `defer`, while the `public/js` bundle is not
  deferred, so that bundle runs first. Code in `public/js` can use `jsml` only inside functions that run later, not at the
  top level of the file.
- All of the library's names are globals: `jsml`, `_`, `html`, `signal`, `memo`, `effect`, `batch`, `untrack`, `read`,
  `render`, `Show`, `For`, `Fragment`, `children`, `createContext`, `useContext`, `onMount`, `onCleanup`, `createRoot`,
  `getOwner`, `runWithOwner`, `fromImpulse`, plus `jsml_*` and `JSML_*` internals. Don't declare top-level `const`, `let`
  or `class` with these names: the side loader concatenates files, so a clash breaks the whole bundle.

---

## Performance and memory

- **Put bindings at the leaves.** `jsml.span(_, count)` updates one text node. A large `() => html\`…\`` block re-creates its
  whole subtree on every change. Use `Show` for conditional blocks and `For` for lists.
- **Keep item identity stable for `For`.** Rows are matched by `===`. If a refresh maps the server data to new objects,
  every row is destroyed and rebuilt. Update the existing items' signals instead, or iterate over primitive keys (ids).
- **Use `batch(() => { … })` for several writes.** Without it, every setter updates the DOM immediately.
- **Use `memo()` for derived values** that are expensive or read in many places. A plain `() => …` is recomputed by every reader.
- **Template parsing is cached per call site**, so calling `html` in loops and components is cheap. Only the first call of
  each literal parses.
- **Lifetimes.** Effects, bindings and Impulse listeners are disposed only by their owner: `render()`'s disposer, or a
  `Show` branch or `For` row being removed. UI built with the proxy API at the top level (outside `render()`) lives for as long as
  the signals it reads do, exactly like the original `Impulse.listen()` (which never unsubscribed). If you rebuild a widget
  often, mount it with `const dispose = render(() => Widget(), container)` and call `dispose()` when replacing it.
- `read(impulse)` inside a binding subscribes anew each time the binding runs. For an Impulse that is read in a hot path,
  create `const value = fromImpulse(impulse)` once and read `value()`.

---

## Differences from the original jsml

All existing calls keep working. The repo's helpers (`Tag`, `CheckBox`, `Radio`, `Link`, `Svg`, `Remote`, `Bar`, …)
produce the same DOM, and `jsml_addProps` and `jsml_addContent` are still there. These are the intentional changes:

| Before | Now |
|---|---|
| The props object you passed was mutated (`class` and `style` deleted) | It is left untouched |
| `class` on SVG was ignored (`className` assignment) | It works |
| A non-`on*` function prop was stringified | It is a live binding (see the gotchas) |
| `null` and booleans as content rendered `"null"` / `"true"` | They render nothing |
| Nested arrays in content were stringified (`[object HTMLElement]`) | They are flattened |
| An element as the first argument logged an error | It is treated as content: `jsml.div(el)` |
| `jsml.div(props, content)`: exactly two arguments | Content is variadic (`jsml.p(_, "a", "b")`), and props can be skipped (`jsml.p([child])`) |
| `typeof jsml === "object"` | `"function"` (it is also callable: `jsml(tag, props, ...children)`) |
| `jsml.AnyName` returned an element factory | Capitalized names are components and throw unless registered |
| `onclick: "string"` threw a generic `TypeError` | It throws a descriptive `TypeError` |
| The `x-*` attributes and the SideLoader importers lived in `Jsml.js` | They are unchanged, in `JsmlSideLoader.js` |
| Types lived in `Jsml.d.ts` | They are JSDoc in `Jsml.js` (`JsmlProps`, `JsmlContent`, `Jsml`, … are still the names) |

---

## API reference

### Elements: `jsml.<tag>(props?, ...content)`

```js
jsml.div()                                        // <div></div>
jsml.div("card active", "text")                   // a string props is the class
jsml.div({ id: "x", dataTestid: "t" }, [a, b])    // content: a node, string, number, array, function, Impulse
jsml.p(_, "a", jsml.b(_, "b"), "c")               // `_` (undefined) skips props; content is variadic
jsml.ul([jsml.li(), jsml.li()])                   // props can be left out when the first argument is content
jsml("div", { id: "x" }, "text")                  // h()-style, use it for dynamic tag names
```

Props:

| Prop | Meaning |
|---|---|
| `class` / `className` | A string, an array (`["a", cond && "b"]`) or an object map (`{ a: true, b: () => isB() }`). Values may be accessors |
| `style` | A string, an object (`{ marginTop: "4px", "--gap": 2, width: () => w() + "px" }`) or an accessor returning either |
| `onClick`, `onInput`, `onKeyDown`, … | A listener for the lower-cased event name |
| `on:exactName` | A listener with a case-sensitive name (custom events) |
| `ref` | `el => …` is called with the element, or `{ current }` is filled in |
| `prop:name` | Sets a DOM property instead of an attribute |
| `attr:name` | Sets an attribute without name conversion |
| `\name` | An escaped key, set literally as the attribute `name` |
| `children` | Content, used when no content arguments are given |
| anything else | An attribute. Functions and Impulses are live, `false`/`null`/`undefined` remove it |

### Reactivity

```js
const [count, setCount] = signal(0);          // accessor + setter
setCount(5); setCount(c => c + 1);            // a value or an updater
const double = memo(() => count() * 2);       // cached derived value
const stop = effect(() => console.log(count()));   // re-runs when what it read changes; stop() disposes it
batch(() => { setA(1); setB(2); });           // dependants run once, at the end
untrack(() => count());                       // read without subscribing
read(maybeReactive);                          // accessor → value, Impulse → value, anything else as is
const value = fromImpulse(impulse);           // Impulse → accessor (the listener is removed with the scope)
```

Existing `Impulse` objects work anywhere an accessor does: in content, attributes, `class` and `style` values, `Show`'s
`when` and `For`'s `each`.

### Components

```js
/** @param {JsmlParentProps<{ title: string }>} props */
function Card(props) {
    return jsml.div("card", [jsml.h3(_, props.title), props.children]);
}

jsml(Card, { title: "Hi" }, jsml.p(_, "body"));          // h()-style
html`<${Card} title="Hi"><p>body</p></>`;                // template
const { Card: CardTag } = jsml.define({ Card });         // registers jsml.Card, returns typed callers
jsml.Card({ title: "Hi" }, "body");                      // registered, untyped
```

`props.children` is the single child itself, or an array when there are several.

### Templates: `` html`…` ``

```js
html`<div class="card" onClick=${handler}>${() => count()}</div>`
html`<input value=${text} onInput=${e => setText(e.target.value)} disabled />`
html`<p title="Hello ${name}">…</p>`                // mixed text + accessor = a live attribute
html`<${Card} ...${props} title="x">children</>`    // spread, component, </> closes it
html`<>fragment</>`                                 // several roots → an array
html`<svg viewBox="0 0 10 10"><circle r="4" /></svg>`   // SVG namespace is automatic
```

Attribute names are used as written (`stroke-width`, `data-id`, `onClick`). A name may contain expressions (`on:${EVENT_NAME}=${fn}`, `data-${key}=…`), but it can't be only an expression: use `...${props}` for that. Entities (`&amp;`, `&nbsp;`, …) and
`<!-- comments -->` are supported.

For DOM APIs there are two variants with the same syntax and caching:

```js
list.append(html.node`<li>${text}</li>`)            // always a Node: one root as is, anything else in a DocumentFragment
const row = html.element`<tr><td>${name}</td></tr>`  // the root element (typed HTMLElement); throws unless there is exactly one
row.dataset.id = id;
```

Live values inside a `html.node` fragment keep updating after the fragment is inserted.

Both also work as plain functions over a `html` result. PhpStorm gives HTML editing support (completion, closing
tags, indenting on Enter) only to templates tagged with plain `html`, so prefer this form in the editor:

```js
const row = html.element(html`<tr><td>${name}</td></tr>`)
list.append(html.node(html`<li>${text}</li>`))
```

### Control flow

```js
Show({ when: isOpen, fallback: "closed", children: () => jsml.div(_, "open") })
html`<${Show} when=${user} fallback=${"Loading…"}>${u => jsml.b(_, () => u().name)}</>`  // child fn gets an accessor of `when`

For({ each: todos, fallback: "Nothing", children: (todo, index) => jsml.li(_, () => index() + 1, ". ", todo.text) })
html`<${For} each=${todos}>${(todo, index) => html`<li>${todo.text}</li>`}</>`
```

`Show` re-creates its content only when the truthiness of `when` flips. `For` is keyed by identity: rows are created
once per item, then moved, and disposed when the item leaves the list. `index` is an accessor.

### Context

```js
const Theme = createContext("light");
html`<${Theme.Provider} value=${theme}><${Child} /></>`;
function Child() { const theme = useContext(Theme); return jsml.p(_, () => read(theme)); }
```

### Lifecycle and mounting

```js
const dispose = render(() => html`<${App} />`, container);   // mounts; dispose() removes the nodes and runs cleanups

function Clock() {
    const [now, setNow] = signal(new Date());
    onMount(() => {
        const id = setInterval(() => setNow(new Date()), 1000);
        onCleanup(() => clearInterval(id));                    // runs when the owning scope is disposed
    });
    return jsml.p(_, () => now().toLocaleTimeString());
}
```

`createRoot(dispose => …)`, `getOwner()` and `runWithOwner(owner, fn)` are available for advanced cases, such as creating
reactive UI inside a callback that runs later and should belong to a component.

### SideLoader / `x-*` attributes

These are unchanged and live in `JsmlSideLoader.js`: `x-get` / `x-post` / … (all HTTP methods), `x-target`, `x-event`,
`x-data`, `x-swap`, `x-init`, and the `jsmlLoad` / `jsmlAdded` events (`JSML_EVENT_LOAD`, `JSML_EVENT_FETCHED`).

---

## Editor support, playground, tests

- **Types.** `Jsml.js` carries full JSDoc types: per-tag props and element return types, typed events with `currentTarget`,
  checked component props, and generic `signal`/`memo`/`For`/`Show`. Add `// @ts-check` to a file to get errors as well as
  completions. Useful types for your own JSDoc are `JsmlProps`, `JsmlContent`, `JsmlComponent<P>`,
  `JsmlParentProps<P>`, `Accessor<T>`, `Setter<T>`, `Signal<T>` and `MaybeReactive<T>`.
  - The types can't see what `jsml.define` registers, so `jsml.Card(...)` is untyped. Use the callers that `define` returns.
  - Unknown prop keys are allowed (for attributes like `dataFoo`), so a misspelled prop name is not reported.
  - Calling `jsml[dynamicTag](…)` doesn't type-check (it's a union of overloads). Use `jsml(dynamicTag, props, …)`.
