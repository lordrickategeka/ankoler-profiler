{{-- resources/views/livewire/partials/network-view.blade.php --}}
<div class="relative w-full h-full bg-white rounded-lg">
    <svg id="relationship-network" class="w-full h-full"></svg>
    
    {{-- Legend --}}
    <div class="absolute top-4 right-4 bg-white rounded-lg shadow-lg p-4 border border-gray-200">
        <h5 class="text-xs font-semibold text-gray-900 mb-2">Legend</h5>
        <div class="space-y-2">
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 rounded-full bg-blue-500"></div>
                <span class="text-xs text-gray-600">Filtered Persons</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 rounded-full bg-purple-500"></div>
                <span class="text-xs text-gray-600">Related Persons</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 rounded-full bg-yellow-500 border-2 border-yellow-600"></div>
                <span class="text-xs text-gray-600">Primary Contact</span>
            </div>
        </div>
    </div>

    {{-- Controls --}}
    <div class="absolute bottom-4 left-4 flex space-x-2">
        <button onclick="zoomIn()" class="px-3 py-2 bg-white rounded-lg shadow hover:bg-gray-50 border border-gray-200">
            <i class="fas fa-plus text-gray-600"></i>
        </button>
        <button onclick="zoomOut()" class="px-3 py-2 bg-white rounded-lg shadow hover:bg-gray-50 border border-gray-200">
            <i class="fas fa-minus text-gray-600"></i>
        </button>
        <button onclick="resetZoom()" class="px-3 py-2 bg-white rounded-lg shadow hover:bg-gray-50 border border-gray-200">
            <i class="fas fa-compress text-gray-600"></i>
        </button>
    </div>
</div>

<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare data for D3
    const persons = @json($persons);
    const filteredIds = @json($filteredPersonIds ?? []);
    
    // Create nodes and links
    const nodes = [];
    const links = [];
    const nodeMap = new Map();
    
    // Add filtered persons as source nodes
    filteredIds.forEach(id => {
        if (!nodeMap.has(id)) {
            const person = persons.find(p => p.id === id);
            if (person) {
                nodes.push({
                    id: id,
                    name: person.full_name || 'Unknown',
                    type: 'source',
                    data: person
                });
                nodeMap.set(id, nodes.length - 1);
            }
        }
    });
    
    // Add related persons and create links
    persons.forEach(person => {
        if (!nodeMap.has(person.id)) {
            nodes.push({
                id: person.id,
                name: person.full_name,
                type: 'related',
                isPrimary: person.primary_relationship !== null,
                data: person
            });
            nodeMap.set(person.id, nodes.length - 1);
        }
        
        // Create links
        if (person.relationships) {
            person.relationships.forEach(rel => {
                links.push({
                    source: nodeMap.get(rel.source_person_id),
                    target: nodeMap.get(person.id),
                    type: rel.type,
                    isPrimary: rel.is_primary
                });
            });
        }
    });
    
    // Set up SVG
    const container = document.getElementById('relationship-network');
    const width = container.clientWidth;
    const height = container.clientHeight;
    
    const svg = d3.select('#relationship-network')
        .attr('width', width)
        .attr('height', height);
    
    // Clear any existing content
    svg.selectAll('*').remove();
    
    const g = svg.append('g');
    
    // Set up zoom
    const zoom = d3.zoom()
        .scaleExtent([0.1, 4])
        .on('zoom', (event) => {
            g.attr('transform', event.transform);
        });
    
    svg.call(zoom);
    
    // Create force simulation
    const simulation = d3.forceSimulation(nodes)
        .force('link', d3.forceLink(links).distance(100).strength(0.5))
        .force('charge', d3.forceManyBody().strength(-300))
        .force('center', d3.forceCenter(width / 2, height / 2))
        .force('collision', d3.forceCollide().radius(40));
    
    // Create links
    const link = g.append('g')
        .selectAll('line')
        .data(links)
        .join('line')
        .attr('stroke', d => d.isPrimary ? '#EAB308' : '#9CA3AF')
        .attr('stroke-width', d => d.isPrimary ? 3 : 1.5)
        .attr('stroke-dasharray', d => d.isPrimary ? '0' : '4,2');
    
    // Create link labels
    const linkLabel = g.append('g')
        .selectAll('text')
        .data(links)
        .join('text')
        .attr('font-size', 9)
        .attr('fill', '#6B7280')
        .attr('text-anchor', 'middle')
        .text(d => d.type.replace('_', ' '));
    
    // Create nodes
    const node = g.append('g')
        .selectAll('circle')
        .data(nodes)
        .join('circle')
        .attr('r', d => d.isPrimary ? 25 : 20)
        .attr('fill', d => {
            if (d.type === 'source') return '#3B82F6';
            if (d.isPrimary) return '#EAB308';
            return '#A855F7';
        })
        .attr('stroke', d => d.isPrimary ? '#CA8A04' : '#fff')
        .attr('stroke-width', d => d.isPrimary ? 3 : 2)
        .style('cursor', 'pointer')
        .call(drag(simulation));
    
    // Add labels
    const label = g.append('g')
        .selectAll('text')
        .data(nodes)
        .join('text')
        .attr('font-size', 11)
        .attr('font-weight', 'bold')
        .attr('fill', '#1F2937')
        .attr('text-anchor', 'middle')
        .attr('dy', 35)
        .text(d => d.name)
        .style('pointer-events', 'none');
    
    // Add tooltips
    node.append('title')
        .text(d => {
            let text = `${d.name}\nType: ${d.type}`;
            if (d.data.person_id) text += `\nID: ${d.data.person_id}`;
            if (d.isPrimary) text += '\n⭐ Primary Contact';
            return text;
        });
    
    // Update positions on each tick
    simulation.on('tick', () => {
        link
            .attr('x1', d => d.source.x)
            .attr('y1', d => d.source.y)
            .attr('x2', d => d.target.x)
            .attr('y2', d => d.target.y);
        
        linkLabel
            .attr('x', d => (d.source.x + d.target.x) / 2)
            .attr('y', d => (d.source.y + d.target.y) / 2);
        
        node
            .attr('cx', d => d.x)
            .attr('cy', d => d.y);
        
        label
            .attr('x', d => d.x)
            .attr('y', d => d.y);
    });
    
    // Drag functionality
    function drag(simulation) {
        function dragstarted(event) {
            if (!event.active) simulation.alphaTarget(0.3).restart();
            event.subject.fx = event.subject.x;
            event.subject.fy = event.subject.y;
        }
        
        function dragged(event) {
            event.subject.fx = event.x;
            event.subject.fy = event.y;
        }
        
        function dragended(event) {
            if (!event.active) simulation.alphaTarget(0);
            event.subject.fx = null;
            event.subject.fy = null;
        }
        
        return d3.drag()
            .on('start', dragstarted)
            .on('drag', dragged)
            .on('end', dragended);
    }
    
    // Zoom controls
    window.zoomIn = () => svg.transition().call(zoom.scaleBy, 1.3);
    window.zoomOut = () => svg.transition().call(zoom.scaleBy, 0.7);
    window.resetZoom = () => svg.transition().call(zoom.transform, d3.zoomIdentity);
});
</script>