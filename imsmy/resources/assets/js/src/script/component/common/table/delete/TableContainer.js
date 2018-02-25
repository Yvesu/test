import React, { Component } from 'react'
import { Table, Pagination ,Icon, Spin, Button } from 'antd'

import fetchPost from 'utils/fetch.js'
// import DropdownMenu from './dropdownMenu/DropdownMenu'

//表格
class TableContainer extends Component {
  constructor(props){
    super(props)
     this.state={
       loading:false,
       stationData:[],
       data:[],
       selectedRowKeys: [],
       selectedRows:[],

     }
  }

  onSelectChange(selectedRowKeys,selectedRows){
    this.setState({ selectedRowKeys})
    //获取批量操作的表格ID
    if(this.props.onChange){
      this.props.onChange(selectedRowKeys)
    }
    //将rowKey改为record.id就可以选中它的id了
  }


  handleChangeFetch=()=>{
    this.setState({loading:true})
    fetchPost.post({
        uri:this.props.uri,
        callback:(res)=>{
          console.log(res,'表格的数据');
          this.setState({
            loading:false,
            stationData:res.tweets,
            data:res.data,
          })
        },
        formData:(this.props.formData? this.props.formData : null)
      })
  }
  handleChangeFetchFormaData=()=>{
      this.setState({loading:true})
      let formData = new FormData()
      if(this.props.search && this.props.search!==''){
        formData.append('name',this.props.search)
      }
      if(this.props.type && this.props.type!==''){
        formData.append('type_id',this.props.type)
      }
      if(this.props.operator && this.props.operator!==''){
        formData.append('operator_id',this.props.operator)
      }
      if(this.props.time && this.props.time!==''){
        formData.append('time',this.props.time)
      }
      if(this.props.duration && this.props.duration!==''){
        formData.append('duration',this.props.duration)
      }
      if(this.props.count && this.props.count!==''){
        formData.append('count',this.props.count)
      }
      //数据请求
      fetchPost.post({
        uri:this.props.uri,
        callback:(res)=>{
          console.log(res,'表格的数据--带参数');
          this.setState({
            loading:false,
            stationData:res.tweets,
            data:res.data
          })
        },
        formData:formData
      })

    }

render() {
    const pagination ={
      showQuickJumper:true,
      showSizeChanger:true
    }
    const { selectedRowKeys, selectedRows } = this.state
    const rowSelection = {
       selectedRowKeys,
       onChange: this.onSelectChange.bind(this)
     }
    const rowKeys = selectedRowKeys.length
    return (
      <Spin spinning={this.state.loading}>
        <div className="table_container">
          <div className={(rowKeys>0) ? "tables_btn_show" : 'tables_btn_hide'}>
            <span className="tables_span">{`选中${rowKeys}个`}</span>
              {
                this.props.DropdownMenu? this.props.DropdownMenu : null
              }
          </div>
          <Table
            rowKey={record => record.id}
            pagination={pagination}
            rowSelection={rowSelection}
            columns={this.props.columns}
            dataSource={this.props.changeData? this.state.stationData : this.state.data}  />
        </div>
      </Spin>
    );
  }
  componentDidMount(){
    this.handleChangeFetch()
  }
  componentWillReceiveProps(nextProps){
    if(this.props.type !== nextProps.type || this.props.search !== nextProps.search ||
      this.props.operator !== nextProps.operator || this.props.time !== nextProps.time ||
      this.props.duration !== nextProps.duration || this.props.count !== nextProps.count
    ){
        this.props.search = nextProps.search
        this.props.type = nextProps.type
        this.props.operator = nextProps.operator
        this.props.time = nextProps.time
        this.props.duration = nextProps.duration
        this.props.count = nextProps.count
        this.handleChangeFetchFormaData()
    }
    if(this.props.RefreshTableState!==nextProps.RefreshTableState){
      this.handleChangeFetch()
    }

      //怎么判断才能重新请求数据呢
  }

}

export default TableContainer
