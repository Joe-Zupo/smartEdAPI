<!DOCTYPE html>
<html lang="en" x-data="apiDocs()" x-init="init()">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Spline+Sans+Mono:ital,wght@0,300..700;1,300..700&display=swap"
        rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Mono:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap"
        rel="stylesheet">
    <title x-text="spec ? spec.info.title + ' — API Docs' : 'API Docs'">API Docs</title>
    @vite(['resources/css/app.css', 'vendor/dedoc/scramble/resources/js/docs.js'])
    <style>
        .font-spline {
            font-family: "Spline Sans Mono", monospace
        }

        .font-sans {
            font-family: "DM Sans", sans-serif
        }
    </style>
</head>

<body class="font-mono bg-zinc-950 text-zinc-100 min-h-screen"
    x-effect="if(currentTab || activeVariantIndex || selected) updateShikiHighlight(generateResponseExample())">
    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <aside class="w-80 bg-zinc-900 border-r border-zinc-800 flex flex-col shrink-0">
            <div class="p-5 border-b font-spline border-zinc-800">
                <h1 class="text-lg font-bold text-white" x-text="spec?.info?.title ?? 'API Docs'"></h1>
                <p class="text-xs text-zinc-500 mt-0.5" x-text="spec ? 'v' + spec.info.version : ''"></p>
            </div>

            {{-- Search --}}
            <div class="px-4 py-3 border-b border-zinc-800">
                <input type="text" x-model="search" placeholder="Search endpoints..."
                    class="w-full bg-zinc-800 text-sm text-zinc-200 placeholder-zinc-500 rounded-md px-3 py-2 outline-none focus:ring-2 focus:ring-blue-400" />
            </div>

            {{-- Endpoint List --}}
            <nav class="overflow-y-auto flex-1 p-3 space-y-1">
                <div class="px-2 flex items-center justify-between mb-2">
                    <h2 class="text-sm font-semibold text-white">ENDPOINTS</h2>
                    <button @click="allOpen = !allOpen"
                        class="flex items-center gap-1 text-xs text-zinc-500 hover:text-zinc-300 transition-colors px-1.5 py-0.5 rounded hover:bg-zinc-800">
                        <svg x-show="allOpen" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                        <svg x-show="!allOpen" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                        <span x-text="allOpen ? 'Collapse all' : 'Expand all'"></span>
                    </button>
                </div>


                <template x-if="!spec">
                    <div class="space-y-2 animate-pulse">
                        <template x-for="i in 6">
                            <div class="h-9 bg-zinc-800 rounded-lg"></div>
                        </template>
                    </div>
                </template>

                <template x-for="(routes, tagName) in filteredRoutes" :key="tagName">
                    <div x-data="{ open: true }" x-effect="open = allOpen" class="space-y-1">

                        <button @click="open = !open"
                            class="flex w-full items-center font-sans justify-between rounded px-2 py-1.5 text-sm font-medium text-zinc-300 hover:bg-zinc-800">
                            <span x-text="tagName"></span>
                            <svg :class="{ 'rotate-90': open }" class="h-4 w-4 transform transition-transform"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <div x-show="open" x-collapse class="pl-3 space-y-1">
                            <template x-for="route in routes" :key="route.path + route.method">
                                <button @click="selected = { ...route, tagName: tagName }"
                                    :class="selected?.operationId === route.operationId ? 'bg-zinc-700/50 text-white' :
                                        'text-zinc-400 hover:bg-zinc-800/60 flex hover:text-white'"
                                    class="w-full text-left flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition">
                                    <span class="text-xs font-medium uppercase px-2 py-0.5 rounded shrink-0"
                                        :class="methodColor(route.method)" x-text="route.method"></span>
                                    <span class="truncate font-mono flex-1 text-xs"
                                        x-text="route.summary || route.operationId"></span>
                                </button>
                            </template>
                        </div>

                    </div>
                </template>
            </nav>
        </aside>

        {{-- Main Panel --}}
        <main class="flex-1 overflow-y-auto bg-zinc-950">

            {{-- Empty state --}}
            <template x-if="!selected">
                <div class="flex flex-col items-center justify-center h-full text-zinc-600 gap-3">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm">Select an endpoint to view details</p>
                </div>
            </template>

            {{-- Endpoint Detail --}}
            <template x-if="selected">
                <div class="pt-18 px-6 max-w-7xl mx-auto w-full">
                    {{-- Title --}}
                    <div>
                        <h1 class="text-white text-zinc-500 font-sans mb-2" x-text="selected.tagName"></h1>
                        <h1 class="text-white text-xl font-spline font-semibold mb-5"
                            x-text="selected.summary ?? selected?.operationId"></h1>
                        <div
                            class="flex w-max text-sm p-2 pr-4 bg-zinc-900 border border-zinc-700/50 items-center rounded-md">
                            <span class="text-xs font-bold uppercase px-2 py-1 rounded block w-max mr-3"
                                :class="methodColor(selected.method)" x-text="selected.method.toUpperCase()"></span>
                            <p class="text-zinc-500" x-show="selected.path" x-text="spec.servers[0].url"></p>
                            <p class="text-zinc-300" x-show="selected.path" x-text="selected.path"></p>
                        </div>
                        <p class="text-zinc-500 text-sm mt-1" x-show="selected.description"
                            x-text="selected.description">
                        </p>
                    </div>
                    <div class="mt-10 flex gap-10">
                        <div class="flex-1 pb-100 space-y-8">
                            {{-- Parameters --}}
                            <template x-if="selected.parameters?.length">
                                <section>
                                    <div class="pb-2 mb-4">
                                        <h2 class="text-lg font-semibold text-white font-spline">Request</h2>
                                    </div>
                                    <div x-show="selected.parameters.filter(p => p.in === 'query').length > 0"
                                        class="mb-10">
                                        <h2 class="font-sans text-sm mb-3 font-semibold text-zinc-400">
                                            Query
                                        </h2>
                                        <div>
                                            <template x-for="p in selected.parameters" :key="p.name">
                                                <div x-show="p.in === 'query'"
                                                    class="border-l group border-zinc-900 hover:border-blue-400/30 transition-all pl-8 pr-4 pb-6">
                                                    <div class="flex items-baseline gap-4">
                                                        <div class="relative">
                                                            <div
                                                                class="absolute h-3 border-l top-1 right-full mr-2 w-6 border-b rounded-bl-xl border-zinc-900 group-hover:border-blue-400/30 transition-all">
                                                                <div
                                                                    class="absolute -top-1 -left-1.25 bg-zinc-700 group-hover:bg-blue-400 border-2 border-zinc-950 transition-all w-2 h-2 rounded-full">
                                                                </div>
                                                            </div>
                                                            <code x-text="p.name"
                                                                class="text-blue-400 text-sm bg-zinc-900/50 border border-zinc-800/60 rounded-md px-1.5 py-0.5"></code>
                                                        </div>
                                                        <span x-text="p.schema.type"
                                                            class="text-sm text-zinc-500"></span>
                                                        <span x-show="p.required"
                                                            class="text-sm text-amber-400 font-sans font-medium">required</span>
                                                    </div>
                                                    <span x-show="p.description"
                                                        class="mt-2 block font-sans pl-2 text-zinc-500 text-sm ml-auto"
                                                        x-text="p.description ?? ''"></span>

                                                    <div x-show="p.schema.enum" class="mt-2 gap-1 pl-2">
                                                        <p class="text-sm text-zinc-500 mb-2">values: </p>
                                                        <div class="flex gap-1 flex-wrap">
                                                            <template x-for="s in p.schema.enum">
                                                                <div x-text="s"
                                                                    class="text-xs text-amber-500 px-1.5 py-0.5 border border-amber-400/10 bg-amber-400/10 rounded-md">
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>

                                                    {{-- <span x-show="p.required"
                                                        class="text-xs text-amber-400 font-medium">required</span> --}}
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div x-show="selected.parameters.filter(p => p.in === 'path').length > 0"
                                        class="mb-10">
                                        <h2 class="font-sans text-sm font-semibold text-zinc-400 mb-3">
                                            Path
                                        </h2>
                                        <div>
                                            <template x-for="p in selected.parameters" :key="p.name">
                                                <div x-show="p.in === 'path'"
                                                    class="border-l border-zinc-900 pl-8 transition-all group hover:border-blue-400/30 pr-4 pb-6 gap-3">
                                                    <div class="flex items-baseline gap-6">
                                                        <div class="relative">
                                                            <div
                                                                class="absolute h-3 border-l top-1 right-full mr-2 w-6 border-b rounded-bl-xl border-zinc-900 group-hover:border-blue-400/30 transition-all">
                                                                <div
                                                                    class="absolute -top-1 -left-1.25 bg-zinc-700 group-hover:bg-blue-400 border-2 border-zinc-950 transition-all w-2 h-2 rounded-full">
                                                                </div>
                                                            </div>
                                                            <code x-text="p.name"
                                                                class="text-sm text-blue-400 bg-zinc-900/50 border border-zinc-800/60 rounded-md px-1.5 py-0.5"></code>
                                                        </div>
                                                        {{-- <span class="text-xs bg-zinc-800 text-zinc-400 px-2 py-0.5 rounded"
                                                    x-text="p.in"></span> --}}
                                                        <span x-show="p.required"
                                                            class="text-sm text-amber-400 font-sans font-medium">required</span>
                                                    </div>
                                                    <span x-show="p.description"
                                                        class="mt-2 pl-2 block text-zinc-500 font-sans text-sm ml-auto"
                                                        x-text="p.description ?? ''"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div>
                                        <h2 class="font-sans text-sm font-semibold text-zinc-400 mb-3">
                                            Header
                                        </h2>
                                        <div>
                                            <template x-for="p in selected.parameters" :key="p.name">
                                                <div x-show="p.in === 'header'"
                                                    class="border-l border-zinc-900 pl-8 transition-all group hover:border-blue-400/30 pr-4 pb-6 gap-3">
                                                    <div class="flex items-baseline gap-6">
                                                        <div class="relative">
                                                            <div
                                                                class="absolute h-3 border-l top-1 right-full mr-2 w-6 border-b rounded-bl-xl border-zinc-900 group-hover:border-blue-400/30 transition-all">
                                                                <div
                                                                    class="absolute -top-1 -left-1.25 bg-zinc-700 group-hover:bg-blue-400 border-2 border-zinc-950 transition-all w-2 h-2 rounded-full">
                                                                </div>
                                                            </div>
                                                            <code x-text="p.name"
                                                                class="text-sm text-blue-400 bg-zinc-900/50 border border-zinc-800/60 rounded-md px-1.5 py-0.5"></code>
                                                        </div>
                                                        {{-- <span class="text-xs bg-zinc-800 text-zinc-400 px-2 py-0.5 rounded"
                                                    x-text="p.in"></span> --}}
                                                        <span x-show="p.required"
                                                            class="text-sm text-amber-400 font-sans font-medium">required</span>
                                                    </div>
                                                    <span x-show="p.description"
                                                        class="mt-2 pl-2 block text-zinc-500 font-sans text-sm ml-auto"
                                                        x-text="p.description ?? ''"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </section>
                            </template>

                            {{-- Request Body --}}
                            <template x-if="selected.requestBody">
                                <section>
                                    <h2 class="font-spline text-sm font-semibold text-zinc-400 mb-3">
                                        Body
                                    </h2>
                                    <div x-data="{ currentSchema: null }"
                                        x-effect="currentSchema = resolveSchema(selected?.requestBody?.content?.['application/json']?.schema)">
                                        <ul>
                                            <template x-for="(fieldDetails, fieldName) in currentSchema?.properties"
                                                :key="fieldName">
                                                <li class="py-4 px-1 border-t border-zinc-900 group">
                                                    <div class="flex items-center gap-4 text-sm">
                                                        <code x-text="fieldName"
                                                            class="text-blue-400 bg-zinc-900/50 border border-zinc-800/60 rounded-md px-1.5 py-0.5"></code>
                                                        <span x-text="fieldDetails.type"
                                                            class="text-sm text-zinc-500"></span>

                                                        <template x-if="currentSchema?.required?.includes(fieldName)">
                                                            <div class="text-sm text-amber-400">
                                                                required
                                                            </div>
                                                        </template>

                                                        {{-- 3. String Length Rules (Min/Max) --}}
                                                        <template
                                                            x-if="fieldDetails.type === 'string' && fieldDetails.minLength !== undefined">
                                                            <div class="px-1.5 py-0.5 text-xs font-mono bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded"
                                                                x-text="'min:' + fieldDetails.minLength"></div>
                                                        </template>
                                                        <template
                                                            x-if="fieldDetails.type === 'string' && fieldDetails.maxLength !== undefined">
                                                            <div class="px-1.5 py-0.5 text-xs font-mono bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded"
                                                                x-text="'max:' + fieldDetails.maxLength"></div>
                                                        </template>

                                                        {{-- 4. Numeric Rule Constraints (Gt/Lt/Min/Max) --}}
                                                        <template
                                                            x-if="(fieldDetails.type === 'integer' || fieldDetails.type === 'number') && fieldDetails.minimum !== undefined">
                                                            <div class="px-1.5 py-0.5 text-xs font-mono bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded"
                                                                x-text="(fieldDetails.exclusiveMinimum ? 'gt:' : 'min:') + fieldDetails.minimum">
                                                            </div>
                                                        </template>
                                                        <template
                                                            x-if="(fieldDetails.type === 'integer' || fieldDetails.type === 'number') && fieldDetails.maximum !== undefined">
                                                            <div class="px-1.5 py-0.5 text-xs font-mono bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 rounded"
                                                                x-text="(fieldDetails.exclusiveMaximum ? 'lt:' : 'max:') + fieldDetails.maximum">
                                                            </div>
                                                        </template>

                                                        {{-- 5. Format/Pattern Type Rule Badge --}}
                                                        <template x-if="fieldDetails.format">
                                                            <div class="px-1.5 py-0.5 text-xs font-mono bg-zinc-800 text-zinc-400 border border-zinc-700/50 rounded"
                                                                x-text="'format:' + fieldDetails.format"></div>
                                                        </template>
                                                    </div>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </section>
                            </template>

                            {{-- Responses --}}
                            <template x-if="selected.responses">
                                <div x-show="selected">
                                    <h2 class="text-lg font-semibold text-white font-spline mb-4">Response</h2>

                                    <div
                                        x-effect="if (selected?.responses) currentTab = Object.keys(selected.responses)[0]">

                                        <div class="flex mb-4 space-x-2">
                                            <template x-for="(responseDetails, statusCode) in selected?.responses"
                                                :key="statusCode">
                                                <button @click="currentTab = statusCode" type="button"
                                                    :class="currentTab === statusCode ?
                                                        'bg-zinc-900/50 text-zinc-200 font-semibold border-zinc-800/50' :
                                                        'border-transparent text-zinc-500 hover:text-zinc-600'"
                                                    class="py-1.5 px-4 border rounded-lg text-sm transition-all focus:outline-none flex items-center space-x-1.5">
                                                    <span
                                                        :class="statusCode.startsWith('2') ? 'bg-green-400' : 'bg-red-400'"
                                                        class="w-2 h-2 rounded-full inline-block"></span>
                                                    <span x-text="statusCode"></span>
                                                </button>
                                            </template>
                                        </div>

                                        <template x-for="(responseDetails, statusCode) in selected?.responses"
                                            :key="statusCode">
                                            <div x-show.immediate="currentTab === statusCode"
                                                :key="statusCode + '_' + activeVariantIndex" x-data="{ responseSchema: null }"
                                                x-effect="if (currentTab === statusCode) { 
             responseSchema = resolveSchema(responseDetails?.content?.['application/json']?.schema || responseDetails); 
         }">

                                                <div class="space-y-4">
                                                    <p class="text-xs text-zinc-500 italic"
                                                        x-text="responseDetails.description"></p>

                                                    <template x-if="responseSchema?.isPolymorphic">
                                                        <div
                                                            class="flex items-center font-sans space-x-2 bg-zinc-900 p-1.5 rounded-lg border border-zinc-800/50 text-xs overflow-x-auto max-w-[596px] w-fit">
                                                            <span
                                                                class="vlock w-max text-zinc-500 font-medium px-2 shrink-0">Response
                                                                Variant:</span>
                                                            <template x-for="(variant, idx) in responseSchema.variants"
                                                                :key="idx">
                                                                <button @click="activeVariantIndex = idx"
                                                                    type="button"
                                                                    :class="activeVariantIndex === idx ?
                                                                        'bg-indigo-500/10 text-indigo-300 border-indigo-500/20 font-semibold shadow-sm' :
                                                                        'text-zinc-600 border-transparent hover:bg-zinc-800/70 hover:text-zinc-400'"
                                                                    class="w-max px-3 border py-1 rounded-md transition-all font-medium whitespace-nowrap"
                                                                    x-text="variant?.properties?.message?.const 
                                ? `State: '${variant.properties.message.const}'` 
                                : `Variant ${idx + 1}`"></button>
                                                            </template>
                                                        </div>
                                                    </template>

                                                    <div :key="'tree_' + statusCode + '_' + activeVariantIndex"
                                                        class="space-y-2 p-4 rounded-md border border-zinc-900">
                                                        <p class="text-sm text-zinc-500 font-medium font-sans">object {
                                                        </p>
                                                        <div x-data="{
                                                            get currentFields() {
                                                                if (!responseSchema) return null;
                                                                if (responseSchema.isPolymorphic) {
                                                                    return responseSchema.variants[activeVariantIndex];
                                                                }
                                                                return responseSchema;
                                                            }
                                                        }">
                                                            <template x-if="currentFields && currentFields.properties">
                                                                <div class="space-y-1">
                                                                    <template
                                                                        x-for="[fieldName, fieldDetails] in Object.entries(currentFields.properties)"
                                                                        :key="fieldName">
                                                                        <div
                                                                            x-html="renderFieldRow(fieldName, fieldDetails, currentFields?.required?.includes(fieldName))">
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>

                                                            <template
                                                                x-if="currentFields && !currentFields.properties && currentFields.type">
                                                                <div class="p-1 animate-fade-in">
                                                                    <span class="text-sm text-zinc-400 italic">No
                                                                        structured data attributes returned for this
                                                                        variant state.</span>
                                                                    <div class="mt-2 font-mono text-xs text-zinc-500">
                                                                        Returns primitive type: <span
                                                                            class="text-indigo-600"
                                                                            x-text="currentFields.type"></span>
                                                                    </div>
                                                                </div>
                                                            </template>
                                                        </div>
                                                        <p class="text-sm text-zinc-500 font-medium font-sans">}</p>

                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                    </div>
                                </div>
                            </template>

                        </div>

                        {{-- <div class="flex-1">
                            Hey
                        </div> --}}
                        {{-- Try It Panel --}}
                        <div class="w-96 shrink-0 flex flex-col gap-10">
                            <div class="w-full" x-data="{
                                token: '',
                                pathParams: {},
                                queryParams: {},
                                bodyParams: {},
                                response: null,
                                responseStatus: null,
                                responseTime: null,
                                loading: false,
                                activeTab: 'params',
                            
                                get resolvedPath() {
                                    let path = selected?.path ?? '';
                                    for (const [k, v] of Object.entries(this.pathParams)) {
                                        path = path.replace(`{${k}}`, encodeURIComponent(v));
                                    }
                                    return path;
                                },
                            
                                get fullUrl() {
                                    const base = (spec?.servers?.[0]?.url ?? '').replace(/\/$/, '');
                                    const query = Object.entries(this.queryParams)
                                        .filter(([, v]) => v !== '')
                                        .map(([k, v]) => `${encodeURIComponent(k)}=${encodeURIComponent(v)}`)
                                        .join('&');
                                    return base + this.resolvedPath + (query ? '?' + query : '');
                                },
                            
                                initParams() {
                                    this.pathParams = {};
                                    this.queryParams = {};
                                    this.bodyParams = {};
                                    this.response = null;
                                    this.responseStatus = null;
                                    this.responseTime = null;
                            
                                    // Detect path and query params
                                    for (const p of (selected?.parameters ?? [])) {
                                        if (p.in === 'path') this.pathParams[p.name] = '';
                                        else if (p.in === 'query') this.queryParams[p.name] = '';
                                    }
                            
                                    // Initialize Request Body fields safely
                                    const bodySchema = this.resolveSchema(selected?.requestBody?.content?.['application/json']?.schema);
                                    if (bodySchema?.properties) {
                                        for (const key of Object.keys(bodySchema.properties)) {
                                            this.bodyParams[key] = '';
                                        }
                                    }
                            
                                    // DYNAMIC AUTH: Automatically scan endpoint specs for headers or establish fallback tokens
                                    const headers = (selected?.parameters ?? []).filter(p => p.in === 'header');
                            
                                    // Check if a CSRF Header or generic Bearer validation exists, pre-populating defaults
                                    const hasCsrfRequirement = headers.some(p => p.name.toLowerCase().includes('csrf') || p.name.toLowerCase().includes('xsrf'));
                                    if (hasCsrfRequirement && !this.token) {
                                        // Try to pull an active Sanctum/Laravel cookie token value locally if present
                                        const match = document.cookie.match(new RegExp('(^| )XSRF-TOKEN=([^;]+)'));
                                        if (match) this.token = decodeURIComponent(match[2]);
                                    }
                                },
                            
                                async send() {
                                    this.loading = true;
                                    this.response = null;
                                    this.responseStatus = null;
                                    this.responseTime = null;
                                    const start = performance.now();
                            
                                    try {
                                        // 1. Replicating Axios defaults: Set base JSON acceptance headers
                                        const headers = {
                                            'Accept': 'application/json',
                                            // CRITICAL FOR LARAVEL: Tells the server this is an AJAX/Fetch request
                                            'X-Requested-With': 'XMLHttpRequest'
                                        };
                            
                                        // 2. Replicating Axios XSRF extraction:
                                        // Automatically scan browser cookies for Laravel's token, exactly like Axios does
                                        const match = document.cookie.match(new RegExp('(^| )XSRF-TOKEN=([^;]+)'));
                                        if (match) {
                                            const decodedToken = decodeURIComponent(match[2]);
                            
                                            // Replicating Axios 'xsrfHeaderName': Inject the header automatically
                                            headers['X-XSRF-TOKEN'] = decodedToken;
                            
                                            // Also map it across any custom header inputs expected by the schema documentation
                                            for (const p of (selected?.parameters ?? [])) {
                                                if (p.in === 'header' && (p.name.toLowerCase().includes('csrf') || p.name.toLowerCase().includes('xsrf'))) {
                                                    headers[p.name] = decodedToken;
                                                }
                                            }
                                        }
                            
                                        // If you are also using standard JWT token authentication fallbacks
                                        if (this.token && !match) {
                                            headers['Authorization'] = `Bearer ${this.token}`;
                                        }
                            
                                        // 3. Process application payload requirements
                                        const hasBody = selected?.method?.toUpperCase() !== 'GET' && selected?.requestBody;
                                        if (hasBody) headers['Content-Type'] = 'application/json';
                            
                                        const opts = {
                                            method: selected.method.toUpperCase(),
                                            headers: headers,
                            
                                            // =========================================================================
                                            // CRITICAL: THIS IS THE EXACT NATIVE EQUIVALENT TO axios.withCredentials = true
                                            // =========================================================================
                                            credentials: 'include'
                                        };
                            
                                        if (hasBody) {
                                            const body = {};
                                            for (const [k, v] of Object.entries(this.bodyParams)) {
                                                if (v !== '') body[k] = v;
                                            }
                                            opts.body = JSON.stringify(body);
                                        }
                            
                                        const res = await fetch(this.fullUrl, opts);
                                        this.responseStatus = res.status;
                                        this.responseTime = Math.round(performance.now() - start);
                            
                                        try {
                                            this.response = JSON.stringify(await res.json(), null, 2);
                                            updateShikiHighlight(this.response, true)
                                        } catch {
                                            this.response = await res.text();
                                        }
                                    } catch (e) {
                                        this.response = `Network error: ${e.message}`;
                                        this.responseTime = Math.round(performance.now() - start);
                                    } finally {
                                        this.loading = false;
                                    }
                                },
                            
                                // UTILITY METHOD: Smoothly hits the backend cookie sanitization routes to reset cross-site states
                                async refreshCsrfToken() {
                                    try {
                                        // Automatically targets standard web cookie endpoints (Sanctum/Web scopes)
                                        await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' });
                            
                                        // Re-read and apply cookie matching sequences instantly
                                        const match = document.cookie.match(new RegExp('(^| )XSRF-TOKEN=([^;]+)'));
                                        if (match) {
                                            this.token = decodeURIComponent(match[2]);
                                            console.log('CSRF Sync Complete.');
                                        }
                                    } catch (err) {
                                        console.error('Failed to clear state layers:', err);
                                    }
                                },
                            
                                statusColor(code) {
                                    if (!code) return 'text-zinc-400';
                                    if (code < 300) return 'text-green-400';
                                    if (code < 400) return 'text-yellow-400';
                                    return 'text-red-400';
                                }
                            }" x-effect="if (selected) initParams()">

                                <div class="space-y-4 border rounded-lg bg-zinc-900/50 border-zinc-800/50 p-4">
                                    {{-- Panel header --}}
                                    <div class="flex items-center justify-between">
                                        <h2 class="text-sm font-semibold text-white font-sans">Try it</h2>
                                        <button @click="send()" :disabled="loading"
                                            class="flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold px-3 py-1.5 rounded-md font-sans transition-colors">
                                            <svg x-show="!loading" class="w-3 h-3" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M5 3l14 9-14 9V3z" />
                                            </svg>
                                            <svg x-show="loading" class="w-3 h-3 animate-spin" fill="none"
                                                viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4" />
                                                <path class="opacity-75" fill="currentColor"
                                                    d="M4 12a8 8 0 018-8v8z" />
                                            </svg>
                                            <span x-text="loading ? 'Sending...' : 'Send'"></span>
                                        </button>
                                    </div>

                                    {{-- URL preview --}}
                                    <div class="bg-zinc-900 border border-zinc-800 rounded-lg px-3 py-2 font-mono text-xs text-zinc-400 break-all"
                                        x-text="fullUrl"></div>

                                    {{-- Bearer token --}}
                                    <div x-show="selected">
                                        <label class="block text-xs mb-1 font-medium transition-colors"
                                            :class="selected?.parameters?.some(p => p.in === 'header' && (p.name
                                                    .toLowerCase()
                                                    .includes('csrf') || p.name.toLowerCase().includes('xsrf'))) ?
                                                'text-indigo-400' : 'text-zinc-500'"
                                            x-text="selected?.parameters?.some(p => p.in === 'header' && (p.name.toLowerCase().includes('csrf') || p.name.toLowerCase().includes('xsrf'))) 
                ? 'Active Session / CSRF Token' 
                : 'Bearer Authentication Token'">
                                        </label>

                                        <div class="relative flex items-center">
                                            <input
                                                :type="selected?.parameters?.some(p => p.in === 'header' && (p.name
                                                    .toLowerCase().includes('csrf') || p.name.toLowerCase()
                                                    .includes('xsrf'))) ? 'text' : 'password'"
                                                x-model="token"
                                                :placeholder="selected?.parameters?.some(p => p.in === 'header' && (p.name
                                                        .toLowerCase().includes('csrf') || p.name.toLowerCase()
                                                        .includes('xsrf'))) ?
                                                    'Syncing session cookie value...' :
                                                    'Enter Bearer token (eyJ...)'"
                                                class="w-full bg-zinc-900 border text-zinc-200 text-xs rounded-md pl-3 pr-8 py-2 outline-none transition-all placeholder-zinc-600 font-mono"
                                                :class="selected?.parameters?.some(p => p.in === 'header' && (p.name
                                                        .toLowerCase().includes('csrf') || p.name.toLowerCase()
                                                        .includes('xsrf'))) ?
                                                    'border-indigo-950/60 focus:border-indigo-500/50 text-indigo-300' :
                                                    'border-zinc-800 focus:border-indigo-500 text-zinc-200'" />

                                            {{-- Small indicator showing if a token value is actively filled --}}
                                            <div class="absolute right-2.5 flex items-center pointer-events-none">
                                                <span class="w-1.5 h-1.5 rounded-full"
                                                    :class="token ? 'bg-green-500' : 'bg-zinc-700'"></span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Tabs: Params / Body --}}
                                    <div class="flex border-b border-zinc-800 text-xs">
                                        <button @click="activeTab = 'params'"
                                            :class="activeTab === 'params' ? 'border-indigo-500 text-indigo-400' :
                                                'border-transparent text-zinc-500 hover:text-zinc-300'"
                                            class="px-3 py-1.5 border-b-2 transition-colors font-medium">Params</button>
                                        <button @click="activeTab = 'body'"
                                            :class="activeTab === 'body' ? 'border-indigo-500 text-indigo-400' :
                                                'border-transparent text-zinc-500 hover:text-zinc-300'"
                                            class="px-3 py-1.5 border-b-2 transition-colors font-medium"
                                            x-show="selected?.requestBody">Body</button>
                                    </div>

                                    {{-- Params tab --}}
                                    <div x-show="activeTab === 'params'" class="space-y-3">
                                        {{-- Path params --}}
                                        <template x-if="Object.keys(pathParams).length > 0">
                                            <div class="space-y-2">
                                                <p class="text-xs text-zinc-600 uppercase font-semibold tracking-wide">
                                                    Path
                                                </p>
                                                <template x-for="(_, key) in pathParams" :key="key">
                                                    <div>
                                                        <label class="block text-xs text-zinc-400 mb-1 font-mono"
                                                            x-text="key"></label>
                                                        <input type="text" x-model="pathParams[key]"
                                                            :placeholder="key"
                                                            class="w-full bg-zinc-900 border border-zinc-800 focus:border-indigo-500 text-zinc-200 text-xs rounded-md px-3 py-1.5 outline-none transition-colors placeholder-zinc-600" />
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        {{-- Query params --}}
                                        <template x-if="Object.keys(queryParams).length > 0">
                                            <div class="space-y-2">
                                                <p class="text-xs text-zinc-600 uppercase font-semibold tracking-wide">
                                                    Query</p>
                                                <template x-for="(_, key) in queryParams" :key="key">
                                                    <div>
                                                        <label class="block text-xs text-zinc-400 mb-1 font-mono"
                                                            x-text="key"></label>
                                                        <input type="text" x-model="queryParams[key]"
                                                            :placeholder="key"
                                                            class="w-full bg-zinc-900 border border-zinc-800 focus:border-indigo-500 text-zinc-200 text-xs rounded-md px-3 py-1.5 outline-none transition-colors placeholder-zinc-600" />
                                                    </div>
                                                </template>
                                            </div>
                                        </template>

                                        <template
                                            x-if="Object.keys(pathParams).length === 0 && Object.keys(queryParams).length === 0">
                                            <p class="text-xs text-zinc-600 italic">No parameters for this endpoint.
                                            </p>
                                        </template>
                                    </div>

                                    {{-- Body tab --}}
                                    <div x-show="activeTab === 'body' && selected?.requestBody" class="space-y-2">
                                        <template x-if="Object.keys(bodyParams).length > 0">
                                            <div class="space-y-2">
                                                <template x-for="(_, key) in bodyParams" :key="key">
                                                    <div>
                                                        <label class="block text-xs text-zinc-400 mb-1 font-mono"
                                                            x-text="key"></label>
                                                        <input type="text" x-model="bodyParams[key]"
                                                            :placeholder="key"
                                                            class="w-full bg-zinc-900 border border-zinc-800 focus:border-indigo-500 text-zinc-200 text-xs rounded-md px-3 py-1.5 outline-none transition-colors placeholder-zinc-600" />
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Response --}}
                                    <template x-if="response !== null">
                                        <div class="space-y-2">
                                            <div class="flex items-center justify-end gap-2">
                                                <span class="w-2 h-2 rounded-full"
                                                    :class="statusBgColor(currentTab)"></span>
                                                <span class="text-xs font-semibold"
                                                    :class="statusColor(responseStatus)"
                                                    x-text="responseStatus"></span>
                                                <span class="text-xs text-zinc-600"
                                                    x-text="responseTime + 'ms'"></span>
                                            </div>

                                            <template x-if="highlightedApiResp">
                                                <div x-html="highlightedApiResp"
                                                    class="leading-relaxed [&>pre]:bg-transparent! bg-zinc-900 border border-zinc-800 rounded-lg p-3 text-xs text-zinc-300 overflow-x-auto max-h-96 overflow-y-auto whitespace-pre-wrap break-words font-mono">
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                </div>
                            </div>

                            {{-- Dynamic Response Example Card Container --}}
                            <div"
                                class="border border-zinc-800 bg-zinc-950 rounded-lg w-full overflow-hidden font-mono"
                                x-show="generateResponseExample()">
                                <div
                                    class="flex items-center justify-between px-4 py-2 border-b border-zinc-800 bg-zinc-900/50 select-none">
                                    <span class="text-xs text-zinc-400 font-medium">Response Payload Example</span>
                                    <div class="flex items-center space-x-1.5">
                                        <span class="w-2 h-2 rounded-full" :class="statusBgColor(currentTab)"></span>
                                        <span class="text-[10px] uppercase tracking-wider text-zinc-500 font-bold"
                                            x-text="currentTab"></span>
                                    </div>
                                </div>
                                <div
                                    class="p-4 overflow-x-auto max-h-[350px] scrollbar-thin scrollbar-thumb-zinc-800 text-xs shiki-code-wrapper">
                                    <template x-if="highlightedJson">
                                        <div x-html="highlightedJson" class="leading-relaxed [&>pre]:bg-transparent!">
                                        </div>
                                    </template>
                                    <template x-if="!highlightedJson">
                                        <pre class="text-zinc-600 font-mono"><code>Generating playground syntax tree...</code></pre>
                                    </template>
                                </div>
                        </div>
                    </div>
                </div>
    </div>
    </template>
    </main>


    {{-- Floating CSRF Cookie Utilities --}}
    <div class="fixed bottom-6 right-6 z-50 flex items-center space-x-2 animate-fade-in" x-show="selected">
        <button
            @click="if (window.initParams) refreshCsrfToken(); else { 
                // Fallback direct discovery fallback trigger matching global scope bindings
                fetch('/sanctum/csrf-cookie', { credentials: 'include' }).then(() => {
                    const match = document.cookie.match(new RegExp('(^| )XSRF-TOKEN=([^;]+)'));
                    if (match && selected) {
                        const tokenInput = document.querySelector('input[type=\'password\']');
                        if (tokenInput) {
                            tokenInput.value = decodeURIComponent(match[2]);
                            tokenInput.dispatchEvent(new Event('input'));
                        }
                    }
                });
            }"
            type="button" title="Refresh CSRF Cookie State"
            class="group flex items-center space-x-1.5 bg-zinc-900 hover:bg-zinc-800 text-zinc-300 hover:text-white border border-zinc-800 hover:border-zinc-700 px-3 py-2 rounded-lg text-xs font-mono transition-all shadow-xl active:scale-95">
            <svg class="w-3.5 h-3.5 text-indigo-400 group-hover:rotate-180 transition-transform duration-300"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.253 8H18" />
            </svg>
            <span>Sync Session Cookies</span>
        </button>
    </div>
    </div>

    <script>
        function apiDocs() {
            return {
                spec: null,
                selected: null,
                search: '',
                allOpen: true,
                groupedRoutes: {},
                activeVariantIndex: 0,
                currentTab: '',
                highlighter: null,
                highlightedJson: '',
                highlightedApiResp: '',

                async init() {
                    try {
                        const shiki = await import('https://esm.sh/shiki@1.0.0');

                        this.highlighter = await shiki.getHighlighter({
                            themes: ['one-dark-pro'],
                            langs: ['json'],
                        });
                    } catch (e) {
                        console.error("Shiki initialization failed:", e);
                    }

                    try {
                        const res = await fetch('/docs/api.json');
                        this.spec = await res.json();

                        this.groupRoutesByTags();
                        console.log(this.spec)

                        this.updateShikiHighlight(this.generateResponseExample())
                    } catch (e) {
                        console.error("Failed loading OpenAPI specifications:", e);
                    }

                },

                updateShikiHighlight(rawJson, resp = false) {
                    if (!rawJson) {
                        this.highlightedJson = '';
                        return;
                    }

                    if (this.highlighter) {

                        if (resp) {
                            this.highlightedApiResp = this.highlighter.codeToHtml(rawJson, {
                                lang: 'json',
                                theme: 'one-dark-pro',
                            });
                        } else {
                            this.highlightedJson = this.highlighter.codeToHtml(rawJson, {
                                lang: 'json',
                                theme: 'one-dark-pro',
                            });
                        }
                    } else {
                        if (resp) {
                            this.highlightedApiResp = `<pre class="text-emerald-400"><code>${rawJson}</code></pre>`;
                        } else {
                            this.highlightedJson = `<pre class="text-emerald-400"><code>${rawJson}</code></pre>`;
                        }
                    }
                },

                resolveSchema(schema) {
                    if (!schema) return null;

                    const rawSchema = Alpine.raw(schema);

                    // 0. Handle allOf — merge all sub-schemas into one flat object schema
                    if (rawSchema.allOf && Array.isArray(rawSchema.allOf)) {
                        let mergedProperties = {};
                        let mergedRequired = [];
                        for (const subSchema of rawSchema.allOf) {
                            const resolved = this.resolveSchema(subSchema);
                            if (!resolved) continue;
                            const raw = Alpine.raw(resolved);
                            if (raw.properties) {
                                mergedProperties = {
                                    ...mergedProperties,
                                    ...raw.properties
                                };
                            }
                            if (raw.required) {
                                mergedRequired = [...mergedRequired, ...raw.required];
                            }
                        }
                        if (Object.keys(mergedProperties).length > 0) {
                            return {
                                type: 'object',
                                properties: mergedProperties,
                                required: [...new Set(mergedRequired)]
                            };
                        }
                    }

                    // 1. Detect Root Polymorphic Variants (anyOf / oneOf)
                    if (rawSchema.anyOf && Array.isArray(rawSchema.anyOf)) {
                        // Map out every independent response structure variant cleanly
                        const variants = rawSchema.anyOf.map(subSchema => {
                            // Bypass any nested ref links or wrappers inside individual variants
                            return this.resolveSchema(subSchema);
                        }).filter(Boolean);

                        if (variants.length > 1) {
                            return {
                                isPolymorphic: true,
                                variants: variants
                            };
                        }

                        // Fallback if there's only 1 valid sub-schema anyway
                        return variants[0] || null;
                    }

                    // 2. Handle $ref pointers
                    if (rawSchema.$ref) {
                        const pathParts = rawSchema.$ref.replace('#/', '').split('/');
                        let current = Alpine.raw(this.spec);

                        for (const part of pathParts) {
                            if (current && current[part]) {
                                current = Alpine.raw(current[part]);
                            } else {
                                return null;
                            }
                        }
                        return this.resolveSchema(current);
                    }

                    // 3. Handle global Response component wrappers
                    if (rawSchema.content?.['application/json']?.schema) {
                        return this.resolveSchema(rawSchema.content['application/json'].schema);
                    }

                    return rawSchema;
                },

                generateResponseExample() {
                    if (!this.selected?.responses) return '';
                    const responseDetails = this.selected.responses[this.currentTab];

                    const resolved = this.resolveSchema(responseDetails?.content?.['application/json']?.schema ||
                        responseDetails);
                    if (!resolved) return '';

                    const walk = (node) => {
                        const resNode = this.resolveSchema(node);
                        if (!resNode) return null;

                        const rawNode = Alpine.raw(resNode);

                        if (rawNode.isPolymorphic && Array.isArray(rawNode.variants)) {
                            const targetVariant = rawNode.variants[this.activeVariantIndex] || rawNode.variants[0];
                            return walk(targetVariant);
                        }

                        if (typeof rawNode.const !== 'undefined') return rawNode.const;
                        if (typeof rawNode.default !== 'undefined') return rawNode.default;
                        if (typeof rawNode.example !== 'undefined') return rawNode.example;

                        const objectProperties = rawNode.properties || null;
                        const type = rawNode.type || (objectProperties ? 'object' : 'string');

                        if (rawNode.enum && Array.isArray(rawNode.enum)) {
                            return rawNode.enum[0];
                        }

                        switch (type) {
                            case 'string':
                                if (rawNode.format === 'date-time') return new Date().toISOString();
                                if (rawNode.format === 'date') return new Date().toISOString().split('T')[0];
                                return "string";
                            case 'integer':
                            case 'number':
                                return 0;
                            case 'boolean':
                                return true;
                            case 'array':
                                const arrayTarget = rawNode.items || (rawNode.prefixItems && rawNode.prefixItems[0]);
                                return arrayTarget ? [walk(arrayTarget)] : [];
                            case 'object':
                                if (!objectProperties) return {};
                                const obj = {};
                                for (const [key, propDetails] of Object.entries(objectProperties)) {
                                    obj[key] = walk(propDetails);
                                }
                                return obj;
                            default:
                                return null;
                        }
                    };

                    try {
                        return JSON.stringify(walk(resolved), null, 2);
                    } catch (e) {
                        return `// Example generation error: ${e.message}`;
                    }
                },

                renderFieldRow(name, details, isRequired, depth = 0) {
                    if (!details) return '';

                    let resolved = this.resolveSchema(details);
                    if (!resolved) return '';

                    const rawResolved = Alpine.raw(resolved);

                    const type = rawResolved.type || 'object';
                    const description = rawResolved.description || '';
                    const requiredBadge = isRequired ?
                        `<span class="text-amber-400 text-xs font-medium ml-1">required</span>` :
                        '';
                    const hasChildren = rawResolved.properties || (type === 'array' && rawResolved.items) || (type ===
                        'array' && rawResolved.prefixItems);

                    // Format the enum options into a tag list if they exist
                    let enumValueHtml = '';
                    if (rawResolved.enum && Array.isArray(rawResolved.enum)) {
                        enumValueHtml = `
            <div class="mt-0.5 flex flex-wrap gap-1 items-center font-sans text-xs">
                <span class="text-zinc-400 italic">Options:</span>
                ${rawResolved.enum.map(val => `<code class="bg-zinc-100 border border-zinc-200 text-indigo-600 px-1 rounded font-mono text-[11px]">${val}</code>`).join('')}
            </div>
        `;
                    }

                    const rowHeaderHtml = `
        <div class="font-sans py-1 hover:bg-zinc-900 rounded px-1 transition-colors">
            <div class="flex items-baseline space-x-2 select-none group cursor-pointer">
                ${hasChildren ? `
                                                                                <svg class="w-3 h-3 text-zinc-500 group-hover:text-zinc-300 transform transition-transform duration-150 [[open]>summary_&]:rotate-90 mr-1 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                                                            </svg> ` : '<span class="w-2 inline-block"></span>'}
                <span class="font-mono font-medium text-sm text-blue-400">${name}</span>
                <span class="font-mono text-zinc-500 text-xs">${type}</span>
                ${requiredBadge}
                ${description ? `<span class="text-zinc-500 text-xs truncate max-w-xs block md:inline ml-2" title="${description}">— ${description}</span>` : ''}
            </div>
            ${enumValueHtml}
        </div>
    `;

                    // Object depth execution
                    if (rawResolved.properties) {
                        let childHtml = '';
                        for (const [childName, childDetails] of Object.entries(rawResolved.properties)) {
                            const childRequired = rawResolved.required?.includes(childName);
                            // Resolve any $ref on the child before rendering so nested refs display correctly
                            const resolvedChild = this.resolveSchema(childDetails) ?? childDetails;
                            childHtml += this.renderFieldRow(childName, resolvedChild, childRequired, depth + 1);
                        }

                        return `
            <details open class="collapsible-property-branch group/node">
                <summary class="list-none outline-none focus:outline-none [&::-webkit-details-marker]:hidden">${rowHeaderHtml}</summary>
                <div class="pl-4 ml-2.5 border-l border-zinc-800/70 space-y-2 mt-1">
                    ${childHtml}
                </div>
            </details>
        `;
                    }

                    // Array depth execution — supports both `items` (OAS 3.0) and `prefixItems` (OAS 3.1 tuples)
                    else if (type === 'array' && (rawResolved.items || rawResolved.prefixItems)) {
                        const isTuple = !!rawResolved.prefixItems;
                        const itemSources = isTuple ? rawResolved.prefixItems : [rawResolved.items];

                        let childHtml = '';
                        for (const itemSource of itemSources) {
                            const itemSchema = this.resolveSchema(itemSource);
                            const rawItemSchema = Alpine.raw(itemSchema);

                            if (rawItemSchema && rawItemSchema.properties) {
                                for (const [childName, childDetails] of Object.entries(rawItemSchema.properties)) {
                                    const childRequired = rawItemSchema.required?.includes(childName);
                                    // Resolve any $ref on child before rendering
                                    const resolvedChild = this.resolveSchema(childDetails) ?? childDetails;
                                    childHtml += this.renderFieldRow(childName, resolvedChild, childRequired, depth + 1);
                                }
                            }
                        }

                        if (childHtml) {
                            return `
                <details open class="collapsible-property-branch group/node">
                    <summary class="list-none outline-none focus:outline-none [&::-webkit-details-marker]:hidden">${rowHeaderHtml}</summary>
                    <div class="pl-4 ml-2.5 border-l border-zinc-800/70 space-y-2 mt-1">
                        <div class="text-xs text-zinc-500 font-sans mb-1 font-medium">object {</div>
                        ${childHtml}
                        <div class="text-xs text-zinc-500 font-sans mb-1 font-medium">}[]</div>
                    </div>
                </details>
            `;
                        }
                    }

                    return `<div>${rowHeaderHtml}</div>`;
                },

                allEndpoints() {
                    if (!this.spec?.paths) return [];
                    return Object.entries(this.spec.paths).flatMap(([path, methods]) =>
                        Object.entries(methods).map(([method, op]) => ({
                            path,
                            method,
                            op
                        }))
                    );
                },

                filteredRoutes() {
                    if (!this.search.trim()) {
                        return this.groupedRoutes;
                    }

                    const query = this.search.toLowerCase().trim();
                    const filtered = {};

                    Object.entries(this.groupedRoutes).forEach(([tagName, routes]) => {
                        // Filter the routes inside this specific tag group
                        const matchingRoutes = routes.filter(route => {
                            return route.path.toLowerCase().includes(query) ||
                                route.summary && route.summary.toLowerCase().includes(query) ||
                                route.method.toLowerCase().includes(
                                    query); // Optional: match GET, POST, etc.
                        });

                        // Only add the tag group to the final result if it has matching routes
                        if (matchingRoutes.length > 0) {
                            filtered[tagName] = matchingRoutes;
                        }
                    });

                    return filtered;
                },


                methodColor(method) {
                    return {
                        get: 'bg-blue-400/10 text-blue-300',
                        post: 'bg-green-400/10 text-green-300',
                        put: 'bg-amber-400/10 text-amber-300',
                        patch: 'bg-orange-400/10 text-orange-300',
                        delete: 'bg-red-400/10 text-red-300',
                    } [method.toLowerCase()] ?? 'bg-zinc-600';
                },

                statusBgColor(code) {
                    if (!code) return 'bg-zinc-400';
                    if (code < 300) return 'bg-green-400';
                    if (code < 400) return 'bg-yellow-400';
                    return 'bg-red-400';
                },

                groupRoutesByTags() {
                    const groups = {};

                    Object.entries(this.spec.paths).forEach(([pathName, methods]) => {
                        Object.entries(methods).forEach(([methodName, details]) => {

                            const routeTags = details.tags && details.tags.length > 0 ? details.tags : [
                                'General'
                            ];

                            const primaryTag = routeTags[0];

                            if (!groups[primaryTag]) {
                                groups[primaryTag] = [];
                            }

                            groups[primaryTag].push({
                                path: pathName,
                                method: methodName.toUpperCase(),
                                summary: details.summary || details.description,
                                operationId: details.operationId || `${methodName}-${pathName}`,
                                parameters: details.parameters || [],
                                responses: details.responses || {},
                                requestBody: details?.requestBody ? details?.requestBody : null
                            });
                        });
                    });

                    this.groupedRoutes = groups;
                }
            };
        }
    </script>

</body>

</html>
