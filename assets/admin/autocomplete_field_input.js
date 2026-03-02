import TomSelect from 'tom-select';

const autocomplete_field_input = document.querySelectorAll('[autocomplete="autocomplete"]')

const get_autocomplete_data = (elem) => {
    const separator = elem.dataset.separator ?? '-'
    const choices = JSON.parse(elem.dataset.choices) ?? []
    const create = elem.dataset.create === 'create'
    const multiple = elem.dataset.multiple === 'multiple'

    return {separator, choices, create, multiple, persist: false}
}

const init_autocomplete = (elem) => {
    const data = get_autocomplete_data(elem)
    const items = elem.value.split(data.separator)
    const choices = Array.from(new Set(data.choices.concat(items))).sort().filter(v => v !== "")
    const options = choices.map(s => ({value: s, text: s}))

    new TomSelect(elem, {
        items,
        options,
        create: data.create,
        persist: data.persist,
        delimiter: data.separator,
        maxItems: data.multiple ? null: 1
    })
}

autocomplete_field_input.forEach(e => init_autocomplete(e))
