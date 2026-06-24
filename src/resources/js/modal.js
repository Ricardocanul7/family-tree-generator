export function showPersonInfo(person, translations, modalId, modalContentId) {
    const modal = document.getElementById(modalId);
    const content = document.getElementById(modalContentId);

    const genderLabel = person.gender === 'male'
        ? translations.male
        : person.gender === 'female'
            ? translations.female
            : translations.notSpecified;

    content.innerHTML = `
        <div class="flex justify-between items-start mb-4">
            <h2 class="text-xl font-bold text-gray-800">${person.name}</h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="flex items-center space-x-4 mb-4">
            <img src="${person.photo}" alt="${person.name}" class="w-20 h-20 rounded-full object-cover border-2 ${person.gender === 'female' ? 'border-pink-300' : 'border-blue-300'}"
                onerror="this.src='https://ui-avatars.com/api/?name=${encodeURIComponent(person.name)}&size=200&background=random'">
            <div>
                <p class="text-sm text-gray-600">
                    ${person.birth_date ? '<span class="font-medium">' + translations.birth + '</span> ' + person.birth_date : ''}
                    ${person.death_date ? '<br><span class="font-medium">' + translations.death + '</span> ' + person.death_date : ''}
                </p>
                <p class="text-sm text-gray-600">
                    <span class="font-medium">${translations.gender}</span> ${genderLabel}
                </p>
                <p class="text-sm text-gray-600">
                    <span class="font-medium">${translations.childrenLabel}</span> ${person.children_count || 0}
                </p>
            </div>
        </div>
        ${person.biography ? `
            <div class="mb-4">
                <h3 class="font-semibold text-gray-700 mb-1">${translations.biography}</h3>
                <p class="text-sm text-gray-600">${person.biography}</p>
            </div>
        ` : ''}
        <a href="/tree/${person.id}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            ${translations.viewFullTree} ${person.first_name}
        </a>
    `;

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    const handler = (e) => {
        if (e.target === modal) closeModal(modalId);
    };
    modal.addEventListener('click', handler);
}

export function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
