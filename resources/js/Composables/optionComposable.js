export default async function getOption(key, objKey = null, defaultValue = null) {
  let res = await fetch('/api/options/' + key).then((res) => res.json())
  return objKey ? res[objKey] ?? defaultValue : res
}
