import { execFileSync } from 'node:child_process'
import { readFileSync } from 'node:fs'

const userAgent = process.env.npm_config_user_agent

if (!userAgent || !userAgent.startsWith('npm/')) {
    console.error('Error: Please use npm as the package manager for this project.')
    process.exit(1)
}

const packageManifest = JSON.parse(readFileSync(new URL('./package.json', import.meta.url), 'utf8'))
const minimumVersionPattern = /^>=\s*(\d+)\.(\d+)\.(\d+)$/

const parseMinimumVersion = (engineName, constraint) => {
    const match = typeof constraint === 'string' ? minimumVersionPattern.exec(constraint) : null

    if (match === null) {
        throw new Error(`Unsupported ${engineName} engine constraint: ${String(constraint)}. Expected a minimum version such as ">=24.15.0".`)
    }

    return match.slice(1).map(Number)
}

const parseVersion = (version) => {
    const [major = 0, minor = 0, patch = 0] = version
        .replace(/^v/, '')
        .split('.')
        .slice(0, 3)
        .map((part) => Number.parseInt(part, 10))

    return [major, minor, patch]
}

const isAtLeast = (current, minimum) => {
    for (let index = 0; index < minimum.length; index += 1) {
        if (current[index] !== minimum[index]) {
            return current[index] > minimum[index]
        }
    }

    return true
}

const requiredNodeVersion = parseMinimumVersion('Node.js', packageManifest.engines?.node)
const requiredNpmVersion = parseMinimumVersion('npm', packageManifest.engines?.npm)
const currentNodeVersion = process.versions.node

let currentNpmVersion

try {
    const npmExecutable = process.env.npm_execpath

    if (!npmExecutable) {
        throw new Error('npm did not expose its executable path.')
    }

    currentNpmVersion = execFileSync(process.execPath, [npmExecutable, '--version'], {
        encoding: 'utf8'
    }).trim()
} catch {
    console.error('Error: Could not determine npm version. Is npm installed correctly?')
    process.exit(1)
}

if (!isAtLeast(parseVersion(currentNodeVersion), requiredNodeVersion)) {
    const requiredVersion = requiredNodeVersion.join('.')
    console.error(`Error: Your Node.js version (${currentNodeVersion}) is too old.`)
    console.error(`Please upgrade to Node.js v${requiredVersion} or higher.`)
    process.exit(1)
}

if (!isAtLeast(parseVersion(currentNpmVersion), requiredNpmVersion)) {
    const requiredVersion = requiredNpmVersion.join('.')
    console.error(`Error: Your npm version (${currentNpmVersion}) is too old.`)
    console.error(`Please upgrade to npm v${requiredVersion} or higher.`)
    process.exit(1)
}

console.log('Using npm as expected with compatible Node.js and npm versions.')
