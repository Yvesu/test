import React, { Component } from 'react'
import { Button, Menu, Dropdown, Icon} from 'antd'


import ModalDelete from '../../../../common/modal/ModalDelete'

//下拉菜单
class Screen extends Component {
  constructor(props){
    super(props)
     this.state={
       visible:false
     }

  }
  render() {
    const tableID = this.props.operation? this.props.record : this.props.SelectAll

    return (
      <Dropdown overlay={
        <Menu className="dropMenu" style={{textAlign:"center"}}>
          <Menu.Item  key="1">
             <Button>解除</Button>
          </Menu.Item>
          <Menu.Item  key="2">
            <ModalDelete id={tableID} shotImg={this.props.shotImg}
                onlyDelete={this.props.operation? true : false}
              />
          </Menu.Item>
        </Menu>}
        trigger={['click']}>
              <Button type={this.props.operation ? "" : "primary"} >
                  {this.props.operation ? "操作" : "批量操作"}
                    <Icon type="down" />
              </Button>
      </Dropdown>
      )
  }

}

export default Screen
